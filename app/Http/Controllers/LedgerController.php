<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $accountFilter = $request->input('account');
        $itemFilter = $request->input('item');  // New filter
        $year  = $request->input('year',  Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        // Get all accounts for the user
        $allAccounts = Auth::user()->accounts()->get();

        // Get all unique items for filtering
        $allItems = Ledger::where('user_id', Auth::id())
            ->where('item', '<>', 'Horizon placeholder')
            ->distinct()
            ->pluck('item')
            ->sort()
            ->values();

        // ===== Get confirmed ledgers (most recent 5) =====
        $confirmedQuery = Ledger::with('account')
            ->where('user_id', Auth::id())
            ->where('status', 'confirmed')
            ->when($accountFilter, function ($q) use ($accountFilter) {
                $q->where('account_id', $accountFilter);
            })
            ->when($itemFilter, function ($q) use ($itemFilter) {
                $q->where('item', $itemFilter);
            })
            ->where('item', '<>', 'Horizon placeholder')
            ->orderBy('date', 'desc')
            ->orderBy('effective_time', 'desc')
            ->orderBy('id', 'desc');

        $confirmed = $confirmedQuery->limit(5)->get();

        // ===== Get pending ledgers (10 records) =====
        $pendingQuery = Ledger::with('account')
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->when($accountFilter, function ($q) use ($accountFilter) {
                $q->where('account_id', $accountFilter);
            })
            ->when($itemFilter, function ($q) use ($itemFilter) {
                $q->where('item', $itemFilter);
            })
            ->where('item', '<>', 'Horizon placeholder')
            ->orderBy('date', 'asc')
            ->orderBy('effective_time', 'asc')
            ->orderBy('id', 'asc');

        $pending = $pendingQuery->limit(10)->get();

        // Merge confirmed (reversed) + pending
        $ledgers = collect($confirmed)->reverse()->concat($pending);

        // ===== Get balance by account (use latest confirmed records) =====
        $balanceByAccount = $allAccounts->map(function ($account) use ($accountFilter) {
            if ($accountFilter && $accountFilter != $account->id) {
                return null;
            }
            $latest = Ledger::where('user_id', Auth::id())
                ->where('account_id', $account->id)
                ->where('status', 'confirmed')
                ->orderBy('date', 'desc')
                ->orderBy('effective_time', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            return [
                'account' => $account,
                'balance' => $latest ? $latest->balance : 0,
            ];
        })->filter();

        // Total liquid assets
        $totalLiquidAssets = $balanceByAccount->sum('balance');

        $latestConfirmed = Ledger::where('user_id', Auth::id())
            ->where('status', 'confirmed')
            ->max('date') ?? Carbon::today()->toDateString();

        // Date navigation for year/month buttons
        $currentDate = Carbon::createFromDate($year, $month, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        // Date dropdowns for form (year, month, day with today as default)
        $today = Carbon::today();
        $defaultYear = $today->year;
        $defaultMonth = $today->month;
        $defaultDay = $today->day;

        // Generate year options (±10 years)
        $yearOptions = range($today->year - 10, $today->year + 10);
        
        // Month options are fixed
        $monthOptions = range(1, 12);
        
        // Day options vary by month/year
        $daysInMonth = Carbon::createFromDate($defaultYear, $defaultMonth, 1)->daysInMonth;
        $dayOptions = range(1, $daysInMonth);

        return view('ledgers.index', compact(
            'ledgers',
            'confirmed',
            'pending',
            'allAccounts',
            'allItems',
            'balanceByAccount',
            'latestConfirmed',
            'totalLiquidAssets',
            'year',
            'month',
            'accountFilter',
            'itemFilter',
            'currentDate',
            'prevDate',
            'nextDate',
            'defaultYear',
            'defaultMonth',
            'defaultDay',
            'yearOptions',
            'monthOptions',
            'dayOptions'
        ));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = Auth::user()->accounts()->get();
        return view('ledgers.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // バリデーション
    $request->validate([
        'date' => 'required|date',
        'item' => 'required|string|max:255',
        'amount' => 'required|numeric',
        'account_id' => 'required|integer|exists:accounts,id',
    ]);
    
    $startDate = Carbon::parse($request->input('date'));
    $accountId = $request->input('account_id');
    $endDate = $request->has('end_date') 
        ? Carbon::parse($request->input('end_date'))
        : null; // 繰り返し終了日（選択されていれば取得）

    // endDateがstartDateより前の場合にエラーを出す
    if ($endDate && $endDate->lt($startDate)) {
        return back()->withErrors(['end_date' => 'The end date must be before the start date.']);
    }

    // 繰り返しの単位を確認（月次/年次）
    $repeatMonthly = $request->has('repeat_monthly');
    $repeatYearly = $request->has('repeat_yearly');

    // itemとamountを別の変数に保存
    $item = $request->input('item');
    $amount = $request->input('amount');

    // 新しい取引が追加された後、その取引を含む日付以降の取引を取得（同じuser_id、日付 >= 更新された日付）
    $ledgers = Ledger::where('user_id', Auth::id())
        ->where('account_id', $accountId)
        ->where('date', '>=', $request->date)
        ->orderBy('date', 'asc') // 日付順に並べる
        ->get();

    // 変更される取引の前のbalanceを取得（最初の取引のbalance）
    if ($ledgers->isEmpty()) {
        $balance = 0;  // 取引がない場合は初期状態のbalance
    } else {
        $firstLedger = $ledgers->first();
        $balance = $firstLedger->balance - $request->amount;
    }

    // 新しいgroupIDを生成（繰り返しレコード群に共通のIDを付与）
    $groupID = $repeatMonthly || $repeatYearly ? uniqid('group_') : null; // 繰り返しがない場合はnull

    // insert new ledgers taking account into account

    // 毎月または毎年繰り返しレコードを作成
    if (($repeatMonthly || $repeatYearly) && $endDate) {
        if ($repeatMonthly && !$repeatYearly) {
            // 毎月繰り返し
            while ($startDate <= $endDate) {
                $this->createLedger($startDate, $item, $amount, $groupID, Auth::id(), null, null, $accountId);
                $startDate->addMonth(); // 1ヶ月後に進める
            }
        } elseif ($repeatYearly && !$repeatMonthly) {
            // 毎年繰り返し
            while ($startDate <= $endDate) {
                $this->createLedger($startDate, $item, $amount, $groupID, Auth::id(), null, null, $accountId);
                $startDate->addYear(); // 1年後に進める
            }
        }
    } else {
        // 繰り返しがない場合は1つのレコードを作成
        $this->createLedger($startDate, $item, $amount, $groupID, Auth::id(), null, null, $accountId);
    }

    // 新しい取引が追加された後、その取引を含む日付以降の取引を取得（同じuser_id、日付 >= 更新された日付）
    $updatedLedgers = Ledger::where('user_id', Auth::id())
        ->where('account_id', $accountId)
        ->where('date', '>=', $request->date)
        ->orderBy('date', 'asc') // 日付順に並べる
        ->get();

    foreach ($updatedLedgers as $updatedLedger) {
        $updatedLedger->balance = $balance + $updatedLedger->amount;
        $updatedLedger->save();
        $balance = $updatedLedger->balance;
    }

    // extend horizon for this account
    $this->ensureFiveYearHorizon($accountId);

    // invalidate cached total assets
    Cache::forget('user:' . Auth::id() . ':total_liquid_assets');

    return redirect()->route('ledgers.index')->with('success', 'Ledger records created successfully.');
}

    
    
    // レコード作成処理
    private function createLedger($date, $item, $amount, $groupID, $userId, $transactionId = null, $version = null, $accountId = null)
    {
        $ledger = new Ledger();
        $ledger->user_id = $userId; // ログインユーザーのID
        $ledger->account_id = $accountId;
        $ledger->date = $date->toDateString(); // 日付
        $ledger->item = $item; // アイテム
        $ledger->amount = $amount; // 金額
        $ledger->group_id = $groupID; // グループIDを設定
        // synchronization fields
        $ledger->transaction_id = $transactionId ?? (string) Str::uuid();
        $ledger->version = $version ?? 1;
        $ledger->save(); // 保存
    }
    

    public function show(Ledger $ledger)
    {
        // 詳細表示するためのLedgerデータを取得
        return view('ledgers.show', compact('ledger'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ledger $ledger)
    {
        return view('ledgers.edit', compact('ledger'));
    }

    public function update(Request $request, Ledger $ledger)
    {
        // バリデーション
        $request->validate([
            'date' => 'required|date',
            'item' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'account_id' => 'required|integer|exists:accounts,id',
        ]);
        $accountId = $request->input('account_id');

        // 編集方法の選択（チェックボックス）
        $applyToLaterDates = $request->has('apply_to_later_dates'); // チェックボックスがチェックされているか

        // 更新アクション
        if ($request->input('action') == 'update') {
            // 更新処理
            if ($applyToLaterDates && $ledger->group_id !== null) {
                // 同じgroup_idを持つ、かつ当該データ日付以降のデータを更新
                Ledger::where('user_id', Auth::id())
                    ->where('group_id', $ledger->group_id) // group_idで絞り込み
                    ->where('date', '>=', $ledger->date) // 当該データ日付以降のデータ
                    ->where('account_id', $accountId)
                    ->increment('version');

                Ledger::where('user_id', Auth::id())
                    ->where('group_id', $ledger->group_id) // group_idで絞り込み
                    ->where('date', '>=', $ledger->date) // 当該データ日付以降のデータ
                    ->where('account_id', $accountId)
                    ->update([
                        'item' => $request->item,
                        'amount' => $request->amount,
                        'account_id' => $accountId,
                    ]);
            } else {
                // 当該データのみ編集
                $ledger->date = $request->date;
                $ledger->item = $request->item;
                $ledger->amount = $request->amount;
                $ledger->account_id = $accountId;
                $ledger->group_id = null; // group_idをnullに設定
                $ledger->version = $ledger->version + 1;
                $ledger->save(); // save()を使って更新
            }
        }

        // 削除アクション
        if ($request->input('action') == 'delete') {
            if ($applyToLaterDates && $ledger->group_id !== null) {
                // 同じgroup_idを持つ、かつ当該データ日付以降のデータを削除
                Ledger::where('user_id', Auth::id())
                    ->where('group_id', $ledger->group_id) // group_idで絞り込み
                    ->where('date', '>=', $ledger->date) // 当該データ日付以降のデータ
                    ->where('account_id', $accountId)
                    ->delete();
            } else {
                // 当該データのみ削除
                $ledger->delete();
            }
        }

        // 取引が編集・削除された後、その取引を含む日付以降の取引を取得（同じuser_id、日付 >= 更新された日付）
        $updatedLedgers = Ledger::where('user_id', Auth::id())
            ->where('date', '>=', $request->date)
            ->orderBy('date', 'asc') // 日付順に並べる
            ->get();

        // requestの日付より古いledgerを一つ取得
        $previousLedger = Ledger::where('user_id', Auth::id())
            ->where('account_id', $accountId)
            ->where('date', '<', $request->date)
            ->orderBy('date', 'desc') // 最新の古いデータを取得
            ->first();

        // previousLedgerが存在すれば、そのbalanceを基準に設定
        $balance = $previousLedger ? $previousLedger->balance : 0;

        // 各ledgerに対してbalanceを更新
        foreach ($updatedLedgers as $updatedLedger) {
            // 新しいbalanceを計算
            $updatedLedger->balance = $balance + $updatedLedger->amount;
            $updatedLedger->save(); // 保存
            // 次のledgerのためにbalanceを更新
            $balance = $updatedLedger->balance;
        }

        // after update, ensure we still have a 5-year placeholder
        $this->ensureFiveYearHorizon($accountId);

        // invalidate cached total assets
        Cache::forget('user:' . Auth::id() . ':total_liquid_assets');

        return redirect()->route('ledgers.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Update amount inline (for table editing)
     */
    public function updateInline(Request $request, Ledger $ledger)
    {
        // Verify ownership
        if ($ledger->user_id !== Auth::id()) {
            abort(403);
        }

        // Validate
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $oldAmount = $ledger->amount;
        $newAmount = (int) $request->input('amount');
        $difference = $newAmount - $oldAmount;

        $ledger->amount = $newAmount;
        $ledger->save();

        // Update balances for subsequent ledgers in the same account
        $accountId = $ledger->account_id;
        $userId = Auth::id();

        $subsequentLedgers = Ledger::where('user_id', $userId)
            ->where('account_id', $accountId)
            ->where('date', '>', $ledger->date)
            ->orWhere(function ($q) use ($ledger, $userId, $accountId) {
                $q->where('user_id', $userId)
                  ->where('account_id', $accountId)
                  ->where('date', '=', $ledger->date)
                  ->where('id', '>', $ledger->id);
            })
            ->orderBy('date', 'asc')
            ->orderBy('effective_time', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($subsequentLedgers as $sub) {
            $sub->balance += $difference;
            $sub->save();
        }

        Cache::forget('user:' . Auth::id() . ':total_liquid_assets');

        return response()->json([
            'success' => true,
            'new_balance' => $ledger->balance,
            'message' => 'Updated successfully'
        ]);
    }

    // make sure there is at least one transaction five years beyond the last date for account
    private function ensureFiveYearHorizon($accountId)
    {
        $userId = Auth::id();
        $last = Ledger::where('user_id', $userId)
            ->where('account_id', $accountId)
            ->orderBy('date', 'desc')
            ->first();
        if (!$last) {
            return;
        }
        $target = Carbon::parse($last->date)->addYears(5)->toDateString();
        $exists = Ledger::where('user_id', $userId)
            ->where('account_id', $accountId)
            ->where('date', '>=', $target)
            ->exists();
        if (!$exists) {
            $this->createLedger(Carbon::parse($target), 'Horizon placeholder', 0, null, $userId, null, null, $accountId);
        }
    }

    /**
     * Delete a ledger record
     */
    public function destroy(Ledger $ledger)
    {
        // Verify user owns this ledger
        if ($ledger->user_id !== Auth::id()) {
            abort(403);
        }

        $accountId = $ledger->account_id;
        $ledger->delete();

        // Recalculate balance for later transactions in same account
        if ($accountId) {
            $updatedLedgers = Ledger::where('user_id', Auth::id())
                ->where('account_id', $accountId)
                ->orderBy('date', 'asc')
                ->where('date', '>=', $ledger->date)
                ->get();

            $previousLedger = Ledger::where('user_id', Auth::id())
                ->where('account_id', $accountId)
                ->where('date', '<', $ledger->date)
                ->orderBy('date', 'desc')
                ->first();

            $balance = $previousLedger ? $previousLedger->balance : 0;
            foreach ($updatedLedgers as $updated) {
                $updated->balance = $balance + $updated->amount;
                $updated->save();
                $balance = $updated->balance;
            }
        }

        // Invalidate cache
        Cache::forget('user:' . Auth::id() . ':total_liquid_assets');

        return redirect()->route('ledgers.index')->with('success', 'Record deleted successfully.');
    }

    /**
     * Confirm a pending ledger record (mark as confirmed)
     */
    public function confirm($id)
    {
        $ledger = Ledger::findOrFail($id);

        // Verify ownership
        if ($ledger->user_id !== Auth::id()) {
            abort(403);
        }

        // Only pending records can be confirmed
        if ($ledger->status !== 'pending') {
            return back()->with('error', 'Only pending records can be confirmed.');
        }

        $ledger->status = 'confirmed';
        $ledger->save();

        Cache::forget('user:' . Auth::id() . ':total_liquid_assets');

        return back()->with('success', 'Record confirmed successfully.');
    }

    /**
     * Export ledgers as CSV
     */
    public function export(Request $request)
    {
        $userId = Auth::id();
        $accountFilter = $request->input('account');

        // Get latest confirmed
        $today = Carbon::today()->toDateString();
        $latestConfirmed = Ledger::where('user_id', $userId)
            ->when($accountFilter, function ($q) use ($accountFilter) {
                $q->where('account_id', $accountFilter);
            })
            ->where('date', '<=', $today)
            ->max('date');

        if (!$latestConfirmed) {
            $latestConfirmed = Ledger::where('user_id', $userId)
                ->when($accountFilter, function ($q) use ($accountFilter) {
                    $q->where('account_id', $accountFilter);
                })
                ->max('date') ?: $today;
        }

        // Query matching index filters
        $ledgers = Ledger::with('account')
            ->where('user_id', $userId)
            ->when($accountFilter, function ($q) use ($accountFilter) {
                $q->where('account_id', $accountFilter);
            })
            ->where('date', '<=', $latestConfirmed)
            ->where('item', '<>', 'Horizon placeholder')
            ->orderBy('date', 'desc')
            ->orderBy('effective_time', 'desc')
            ->get();

        // Build CSV
        $csv = "Date,Account,Item,Amount,Balance\n";
        foreach ($ledgers as $ledger) {
            $csv .= sprintf(
                "%s,%s,%s,%d,%d\n",
                $ledger->date,
                optional($ledger->account)->name ?? '',
                '"' . str_replace('"', '""', $ledger->item) . '"',
                $ledger->amount,
                $ledger->balance
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="ledger_' . now()->format('Ymd_His') . '.csv"');
    }

}
