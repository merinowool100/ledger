<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ledgers = Ledger::with('user')
            ->where('user_id', Auth::id())
            ->oldest()
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

        // ログインしているユーザーのデータとして新しいレコードを作成
        $ledger = new Ledger();
        $ledger->user_id = Auth::id(); // ログインユーザーのID
        $ledger->date = $request->date;
        $ledger->item = $request->item;
        $ledger->amount = $request->amount;
        $ledger->save();

        // 成功後、一覧ページにリダイレクト
        return redirect()->route('ledgers.index');
    }

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
