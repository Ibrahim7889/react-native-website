<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function adjust(Wallet $wallet, float $amount, string $type, ?string $referenceType = null, ?int $referenceId = null): Wallet
    {
        return DB::transaction(function () use ($wallet, $amount, $type, $referenceType, $referenceId) {
            $wallet->refresh();
            $wallet->balance = bcadd($wallet->balance, $amount, 2);
            $wallet->save();

            $wallet->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            return $wallet;
        });
    }
}
