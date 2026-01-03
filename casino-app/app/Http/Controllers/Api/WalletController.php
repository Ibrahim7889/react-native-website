<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    public function show(): JsonResponse
    {
        $wallet = request()->user()->wallet;
        return response()->json($wallet);
    }
}
