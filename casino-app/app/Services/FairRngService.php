<?php

namespace App\Services;

use App\Models\Round;

class FairRngService
{
    public function createSeed(): array
    {
        $seed = random_bytes(32);
        $hash = hash('sha256', $seed);

        return [$seed, $hash];
    }

    public function generateResult(Round $round): array
    {
        $number = random_int(0, 36);
        $color = $this->colorForNumber($number);

        return [$number, $color];
    }

    public function colorForNumber(int $number): string
    {
        if ($number === 0) {
            return 'green';
        }

        $red = config('casino.wheel.red', []);
        return in_array($number, $red, true) ? 'red' : 'black';
    }
}
