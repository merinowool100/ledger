<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Ledger;
use Illuminate\Support\Facades\Cache;

class ComputeTotalAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ledger:compute-totals {--chunk=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute total liquid assets per user and cache the result';

    public function handle()
    {
        $chunk = (int) $this->option('chunk');
        User::chunk($chunk, function($users) {
            foreach ($users as $user) {
                $total = 0;
                // get each account's latest non-placeholder ledger and sum balances
                $accounts = $user->ledgers()
                    ->whereNotNull('account_id')
                    ->where('item', '<>', 'Horizon placeholder')
                    ->select(['account_id'])
                    ->groupBy('account_id')
                    ->pluck('account_id');

                foreach ($accounts as $acctId) {
                    $latest = Ledger::where('user_id', $user->id)
                        ->where('account_id', $acctId)
                        ->where('item', '<>', 'Horizon placeholder')
                        ->orderBy('date', 'desc')
                        ->orderBy('effective_time', 'desc')
                        ->orderBy('id', 'desc')
                        ->first(['balance']);
                    if ($latest) $total += intval($latest->balance);
                }

                Cache::put('user:' . $user->id . ':total_liquid_assets', $total, 3600);
                $this->info("User {$user->id} total cached: {$total}");
            }
        });

        return 0;
    }
}
