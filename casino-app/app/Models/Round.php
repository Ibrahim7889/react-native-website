<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'state',
        'seed_hash',
        'seed',
        'result_number',
        'result_color',
        'betting_opened_at',
        'betting_closed_at',
        'resolved_at',
    ];

    protected $casts = [
        'betting_opened_at' => 'datetime',
        'betting_closed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
