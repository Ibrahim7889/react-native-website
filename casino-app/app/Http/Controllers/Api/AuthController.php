<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
            'role' => User::ROLE_USER,
            'locale' => $request->string('locale', 'en'),
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $user = $request->user();
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(): JsonResponse
    {
        $user = request()->user();
        $user->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
