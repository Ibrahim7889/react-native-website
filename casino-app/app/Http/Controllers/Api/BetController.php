<?php

namespace App\Http\Controllers\Api;

use App\Events\WalletUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bets\PlaceBetRequest;
use App\Models\Bet;
use App\Models\Round;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BetController extends Controller
{
    public function store(PlaceBetRequest $request, WalletService $walletService): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $wallet = $user->wallet()->lockForUpdate()->first();

        $round = Round::where('state', Round::STATE_OPEN_BETS)->latest('id')->firstOrFail();

        $amount = (float) $request->validated()['amount'];
        if ($wallet->balance < $amount) {
            return response()->json(['message' => 'Insufficient balance'], 422);
        }

        return DB::transaction(function () use ($walletService, $wallet, $user, $round, $request) {
            $bet = Bet::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'round_id' => $round->id,
                'type' => $request->validated()['type'],
                'selection' => $request->validated()['selection'],
                'amount' => $request->validated()['amount'],
                'status' => Bet::STATUS_PLACED,
            ]);

            $walletService->adjust($wallet, -$bet->amount, 'bet', Bet::class, $bet->id);

            broadcast(new WalletUpdated($wallet->fresh()));

            return response()->json($bet, 201);
        });
    }

    public function history(): JsonResponse
    {
        $bets = Bet::where('user_id', request()->user()->id)
            ->with('round')
            ->latest()
            ->paginate(20);

        return response()->json($bets);
    }
}
