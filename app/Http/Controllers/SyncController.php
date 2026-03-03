<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\StartingBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class SyncController extends Controller
{
    /**
     * GET /sync?since={token}
     * returns a list of ledger changes since the provided token (usually a timestamp)
     * plus the current starting balance state.
     */
    public function fetch(Request $request)
    {
        $userId = Auth::id();
        $since = $request->query('since');

        $query = Ledger::with('account')
            ->select(['id','user_id','account_id','date','item','amount','balance','transaction_id','version','effective_time','updated_at'])
            ->where('user_id', $userId);
        if ($since) {
            // token is ISO timestamp or revision number; we'll treat as timestamp
            $query->where('updated_at', '>', $since);
        }
        if ($request->filled('account')) {
            $query->where('account_id', $request->input('account'));
        }
        $ledgers = $query->orderBy('date')->orderBy('effective_time')->get();

        $maxUpdated = $ledgers->max('updated_at') ?: now();

        // include starting balance info
        $starting = StartingBalance::where('user_id', $userId);
        if ($request->filled('account')) {
            $starting->where('account_id', $request->input('account'));
        }
        $starting = $starting->orderBy('as_of', 'desc')->first();

        return response()->json([
            'ledgers' => $ledgers,
            'starting_balance' => $starting,
            'token' => $maxUpdated->toIso8601String(),
        ]);
    }

    /**
     * POST /sync
     * expects a payload containing an array of ledger patches.
     * each patch should include at least transaction_id and version; other
     * fields may be added, and a special `_deleted` boolean marks deletions.
     */
    public function push(Request $request)
    {
        $userId = Auth::id();
        $patches = $request->input('ledgers', []);
        $conflicts = [];
        $applied = [];

        $touchedAccounts = [];
        foreach ($patches as $patch) {
            // basic validation
            if (empty($patch['transaction_id'])) {
                continue;
            }
            $tx = $patch['transaction_id'];
            $clientVersion = isset($patch['version']) ? intval($patch['version']) : 0;

            $existing = Ledger::where('user_id', $userId)
                ->where('transaction_id', $tx)
                ->first();

            if ($existing) {
                $accountId = $existing->account_id;
                $touchedAccounts[$accountId] = true;

                // allow forced overwrite when client includes force=true
                $force = !empty($patch['force']);
                if ($existing->version !== $clientVersion && !$force) {
                    $conflicts[] = [
                        'transaction_id' => $tx,
                        'server_version' => $existing->version,
                    ];
                    continue;
                }

                if (isset($patch['_deleted']) && $patch['_deleted']) {
                    $existing->delete();
                    continue;
                }

                // update fields, ignore version & transaction_id
                $fields = collect($patch)->except(['transaction_id', 'version', 'force'])->toArray();
                $existing->fill($fields);
                $existing->version = $existing->version + 1;
                $existing->save();
                $applied[] = $existing;
            } else {
                if (isset($patch['_deleted']) && $patch['_deleted']) {
                    // no record to delete, ignore
                    continue;
                }
                $new = new Ledger();
                $fields = collect($patch)->except(['version'])->toArray();
                $new->fill($fields);
                $new->user_id = $userId;
                $new->version = 1;
                $new->save();
                $applied[] = $new;
                $touchedAccounts[$new->account_id] = true;
            }
        }

        // ensure horizon placeholders for any accounts changed
        foreach (array_keys($touchedAccounts) as $acctId) {
            if ($acctId) {
                $this->ensureFiveYearHorizon($acctId);
            }
        }

        if (!empty($conflicts)) {
            return response()->json(['conflicts' => $conflicts], 409);
        }

        // invalidate total assets cache for this user
        Cache::forget('user:' . $userId . ':total_liquid_assets');

        $newToken = now()->toIso8601String();
        return response()->json(['updated' => $applied, 'token' => $newToken]);
    }

    /**
     * GET /sync/tx/{transaction_id}
     * return a single ledger entry for conflict inspection
     */
    public function showTransaction(Request $request, $transaction_id)
    {
        $userId = Auth::id();
        $ledger = Ledger::where('user_id', $userId)
            ->where('transaction_id', $transaction_id)
            ->first();

        if (!$ledger) {
            return response()->json(['message' => 'not found'], 404);
        }

        return response()->json(['ledger' => $ledger]);
    }

    // duplicated helper: ensure ledger exists five years beyond last date for account
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
            $new = new Ledger();
            $new->user_id = $userId;
            $new->account_id = $accountId;
            $new->date = $target;
            $new->item = 'Horizon placeholder';
            $new->amount = 0;
            $new->transaction_id = (string) Str::uuid();
            $new->version = 1;
            $new->save();
        }
    }
}
