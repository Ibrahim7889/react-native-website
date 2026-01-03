<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'round_id',
        'type',
        'selection',
        'amount',
        'payout',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payout' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }
}
