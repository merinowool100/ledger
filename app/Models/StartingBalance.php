<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StartingBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'as_of',
        'balance',
        'revision',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
