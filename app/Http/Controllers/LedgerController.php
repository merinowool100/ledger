<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{

    //クエリパラメーターからyearとmonthを取得
    $year = $request->input('year');
    $month = $request->input('month');

    if ($year && $month) {
        //指定された年月
        $firstDayOfMonth = Carbon::parse("{$year}-{$month}-01")->startOfMonth();
    } else {
        //default
        $firstDayOfMonth = Carbon::now()->startOfMonth();
    }

    $ledgers = Ledger::with('user')
        ->where('user_id', Auth::id())
        ->where('date', '>=', $firstDayOfMonth->toDateString())  // 当月1日以降のデータを取得
        ->orderBy('date', 'asc')
        ->get();

    $prevMonth = $firstDayOfMonth->copy()->subMonth()->startOfMonth();
    $nextMonth = $firstDayOfMonth->copy()->addMonth()->startOfMonth();
    $prevYear = $firstDayOfMonth->copy()->subYear()->startOfMonth();
    $nextYear = $firstDayOfMonth->copy()->addYear()->startOfMonth();

    return view('ledgers.index', compact('ledgers', 'year', 'month', 'prevMonth', 'nextMonth', 'prevYear', 'nextYear', 'firstDayOfMonth'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ledgers.create');
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
    ]);
    
    
    $startDate = Carbon::parse($request->input('date'));
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

    // 毎月または毎年繰り返しレコードを作成
    if (($repeatMonthly || $repeatYearly) && $endDate) {
        if ($repeatMonthly && !$repeatYearly) {
            // 毎月繰り返し
            while ($startDate <= $endDate) {
                $this->createLedger($startDate, $item, $amount, $groupID, Auth::id());
                $startDate->addMonth(); // 1ヶ月後に進める
            }
        } elseif ($repeatYearly && !$repeatMonthly) {
            // 毎年繰り返し
            while ($startDate <= $endDate) {
                $this->createLedger($startDate, $item, $amount, $groupID, Auth::id());
                $startDate->addYear(); // 1年後に進める
            }
        }
    } else {
        // 繰り返しがない場合は1つのレコードを作成
        $this->createLedger($startDate, $item, $amount, $groupID, Auth::id());
    }

    // 新しい取引が追加された後、その取引を含む日付以降の取引を取得（同じuser_id、日付 >= 更新された日付）
    $updatedLedgers = Ledger::where('user_id', Auth::id())
        ->where('date', '>=', $request->date)
        ->orderBy('date', 'asc') // 日付順に並べる
        ->get();

    foreach ($updatedLedgers as $updatedLedger) {
        $updatedLedger->balance = $balance + $updatedLedger->amount;
        $updatedLedger->save();
        $balance = $updatedLedger->balance;
    }

    return redirect()->route('ledgers.index')->with('success', 'Ledger records created successfully.');
}

    
    
    // レコード作成処理
    private function createLedger($date, $item, $amount, $groupID, $userId)
    {
        $ledger = new Ledger();
        $ledger->user_id = $userId; // ログインユーザーのID
        $ledger->date = $date->toDateString(); // 日付
        $ledger->item = $item; // アイテム
        $ledger->amount = $amount; // 金額
        $ledger->group_id = $groupID; // グループIDを設定
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
        ]);

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
                    ->update([
                        'item' => $request->item,
                        'amount' => $request->amount,
                    ]);
            } else {
                // 当該データのみ編集
                $ledger->date = $request->date;
                $ledger->item = $request->item;
                $ledger->amount = $request->amount;
                $ledger->group_id = null; // group_idをnullに設定
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

        return redirect()->route('ledgers.index')->with('success', 'Record updated successfully.');
    }

}