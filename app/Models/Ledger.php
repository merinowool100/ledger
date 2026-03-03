<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;

    // fields used for synchronization and ordering
    protected $fillable = [
        'date',
        'effective_time', // optional time component for ordering
        'item',
        'amount',
        'balance',
        'group_id',
        'account_id',
        'transaction_id',
        'version',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    
    /**
     * Common casts for efficient date handling
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'integer',
        'balance' => 'integer',
    ];

    /**
     * Scope to restrict to a user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to restrict to an account when provided
     */
    public function scopeForAccount($query, $accountId)
    {
        if ($accountId) {
            return $query->where('account_id', $accountId);
        }
        return $query;
    }

    /**
     * Scope to limit to confirmed dates (<= latestConfirmed)
     */
    public function scopeConfirmedUpTo($query, $latestConfirmed)
    {
        return $query->where('date', '<=', $latestConfirmed);
    }
    
}
