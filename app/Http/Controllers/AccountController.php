<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Auth::user()->accounts()->get();
        return view('accounts.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        if ($user->accounts()->count() >= 5) {
            return back()->withErrors(['name' => 'Maximum of 5 accounts allowed.']);
        }

        $user->accounts()->create(['name' => $request->name]);
        return redirect()->route('accounts.index')->with('success', 'Account created');
    }

    public function update(Request $request, Account $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
        $request->validate(['name' => 'required|string|max:255']);
        $account->name = $request->name;
        $account->save();
        return redirect()->route('accounts.index')->with('success', 'Account renamed');
    }

    public function destroy(Account $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Account deleted');
    }
}
