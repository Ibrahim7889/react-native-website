<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Round;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BetSettlementService
{
    public function settle(Collection $bets, Round $round): void
    {
        $payouts = config('casino.payouts');

        DB::transaction(function () use ($bets, $round, $payouts) {
            /** @var Bet $bet */
            foreach ($bets as $bet) {
                [$won, $multiplier] = $this->evaluate($bet, $round, $payouts);
                $bet->status = $won ? Bet::STATUS_WON : Bet::STATUS_LOST;
                $bet->payout = $won ? bcmul($bet->amount, $multiplier, 2) : 0;
                $bet->save();

                if ($bet->payout > 0) {
                    Wallet::whereKey($bet->wallet_id)->lockForUpdate()->first()
                        ->transactions()
                        ->create([
                            'type' => 'payout',
                            'amount' => $bet->payout,
                            'reference_type' => Bet::class,
                            'reference_id' => $bet->id,
                        ]);

                    Wallet::whereKey($bet->wallet_id)->increment('balance', $bet->payout);
                }
            }
        });
    }

    protected function evaluate(Bet $bet, Round $round, array $payouts): array
    {
        $number = $round->result_number;
        $color = $round->result_color;

        return match ($bet->type) {
            'straight' => [$bet->selection == $number, $payouts['straight']],
            'red' => [$color === 'red', $payouts['red']],
            'black' => [$color === 'black', $payouts['black']],
            'odd' => [$number !== 0 && $number % 2 === 1, $payouts['odd']],
            'even' => [$number !== 0 && $number % 2 === 0, $payouts['even']],
            'dozen' => [$this->inDozen($number, $bet->selection), $payouts['dozen']],
            default => [false, 0],
        };
    }

    protected function inDozen(int $number, string $selection): bool
    {
        return match ($selection) {
            '1st' => $number >= 1 && $number <= 12,
            '2nd' => $number >= 13 && $number <= 24,
            '3rd' => $number >= 25 && $number <= 36,
            default => false,
        };
    }
}
