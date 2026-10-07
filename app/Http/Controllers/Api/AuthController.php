<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());
        $token = $user->createToken('api')->plainTextToken;
        return response()->json(['data' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email], 'token' => $token], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();
        if (!$user || !Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }
        return response()->json(['data' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email], 'token' => $user->createToken('api')->plainTextToken]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function user()
    {
        $user = request()->user();

        return response()->json(['data' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]]);
    }
}
