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
    public function index()
    {
        $ledgers = Ledger::with('user')
            ->where('user_id', Auth::id())
            ->orderBy('date', 'asc')
            ->get();
        return view('ledgers.index', compact('ledgers'));
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

        // 入力された日付を取得
        $startDate = Carbon::parse($request->input('date'));
        $endDate = $request->has('end_date') ? Carbon::parse($request->input('end_date')) : null; // 繰り返し終了日（選択されていれば取得）

        // endDateがstartDateより前の場合にエラーを出す
        if ($endDate && $endDate->lt($startDate)) {
            return back()->withErrors(['end_date' => 'The end date must be before the start date.']);
        }


        // 繰り返しの単位を確認（月次/年次）
        $repeatMonthly = $request->has('repeat_monthly');
        $repeatYearly = $request->has('repeat_yearly');

        // dd($repeatMonthly);

        // itemとamountを別の変数に保存
        $item = $request->input('item');
        $amount = $request->input('amount');

        // ログインしているユーザーのデータとして新しいレコードを作成
        // $this->createLedger($startDate, $item, $amount, Auth::id());

        // 毎月または毎年繰り返しレコードを作成
        if (($repeatMonthly || $repeatYearly) && $endDate) {
            if ($repeatMonthly && !$repeatYearly) {
                // 毎月繰り返し
                while ($startDate <= $endDate) {
                    // 新しい日付を設定してレコードを作成
                    $this->createLedger($startDate, $item, $amount, Auth::id());
                    $startDate->addMonth(); // 1ヶ月後に進める
                }
            } elseif ($repeatYearly && !$repeatMonthly) {
                // 毎年繰り返し
                while ($startDate <= $endDate) {
                    // 新しい日付を設定してレコードを作成
                    $this->createLedger($startDate, $item, $amount, Auth::id());
                    $startDate->addYear(); // 1年後に進める
                }
            }
        }
        // dd("Start Date: " . $startDate->toDateString()."End Date: " . $endDate->toDateString());
        return redirect()->route('ledgers.index')->with('success', 'Ledger records created successfully.');
    }

    // レコード作成処理
    private function createLedger($date, $item, $amount, $userId)
    {
        $ledger = new Ledger();
        $ledger->user_id = $userId; // ログインユーザーのID
        $ledger->date = $date->toDateString(); // 日付
        $ledger->item = $item; // アイテム
        $ledger->amount = $amount; // 金額
        $ledger->save(); // 保存
    }

    //     // ログインしているユーザーのデータとして新しいレコードを作成
    //     $ledger = new Ledger();
    //     $ledger->user_id = Auth::id(); // ログインユーザーのID
    //     $ledger->date = $request->date;
    //     $ledger->item = $request->item;
    //     $ledger->amount = $request->amount;
    //     $ledger->save();

    //     // 成功後、一覧ページにリダイレクト
    //     return redirect()->route('ledgers.index');
    // }

    /**
     * Display the specified resource.
     */
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ledger $ledger)
    {
        // バリデーション
        $request->validate([
            'date' => 'required|date',
            'item' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        // 更新処理
        $ledger->update([
            'date' => $request->date,
            'item' => $request->item,
            'amount' => $request->amount,
        ]);

        // 更新後、詳細ページにリダイレクト
        return redirect()->route('ledgers.index', $ledger)->with('success', 'Record updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ledger $ledger)
    {
        // 削除処理
        $ledger->delete();

        // 削除後、一覧ページにリダイレクト
        return redirect()->route('ledgers.index')->with('success', 'Record deleted successfully');
    }
}
