<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use Illuminate\Http\JsonResponse;

class RoundController extends Controller
{
    public function current(): JsonResponse
    {
        $round = Round::latest('id')->first();
        return response()->json($round);
    }
}
