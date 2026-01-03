<?php

namespace App\Services;

use App\Events\BetWindowClosed;
use App\Events\RoundResulted;
use App\Events\RoundStarted;
use App\Models\Bet;
use App\Models\Round;
use Illuminate\Support\Facades\DB;

class RouletteRoundService
{
    public function openBetting(): Round
    {
        [$seed, $hash] = app(FairRngService::class)->createSeed();

        $round = Round::create([
            'state' => Round::STATE_OPEN_BETS,
            'seed_hash' => $hash,
            'seed' => $seed,
            'betting_opened_at' => now(),
        ]);

        broadcast(new RoundStarted($round));

        return $round;
    }

    public function closeBetting(Round $round): Round
    {
        $round->update([
            'state' => Round::STATE_CLOSED,
            'betting_closed_at' => now(),
        ]);

        broadcast(new BetWindowClosed($round));

        return $round;
    }

    public function spin(Round $round): Round
    {
        $round->update(['state' => Round::STATE_SPINNING]);
        return $round;
    }

    public function resolve(Round $round): Round
    {
        [$number, $color] = app(FairRngService::class)->generateResult($round);

        DB::transaction(function () use ($round, $number, $color) {
            $round->update([
                'state' => Round::STATE_RESULTED,
                'result_number' => $number,
                'result_color' => $color,
                'resolved_at' => now(),
            ]);

            $bets = Bet::where('round_id', $round->id)->get();
            app(BetSettlementService::class)->settle($bets, $round);
        });

        broadcast(new RoundResulted($round));

        return $round->fresh();
    }
}
