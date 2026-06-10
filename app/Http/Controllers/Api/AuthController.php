<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'nim' => ['nullable', 'string', 'max:50', 'unique:users,nim'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nim' => $validated['nim'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
            'push_notifications_enabled' => true,
            'email_notifications_enabled' => true,
            'biometric_login_enabled' => false,
        ]);

        Cart::firstOrCreate(['user_id' => $user->id]);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'token' => $user->createToken('flutter-token')->plainTextToken,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required_without:nim', 'string'],
            'nim' => ['required_without:email', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = $request->input('email') ?? $request->input('nim');

        $user = User::where(function ($query) use ($identifier) {
            $query->where('email', $identifier)
                  ->orWhere('nim', $identifier);
        })->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email, Nomor Alumni (NIM), atau password tidak sesuai.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Akun tidak aktif.'], 403);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $user->createToken('flutter-token')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['defaultAddress']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
