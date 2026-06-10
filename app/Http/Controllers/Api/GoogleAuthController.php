<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    /**
     * Authenticate or register a customer using Google Sign-In.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loginWithGoogle(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $idToken = $request->input('id_token');
        $email = null;
        $name = null;
        $googleId = null;

        // Bypassing real verification in local/testing environments when a mock token is supplied
        if ($idToken === 'mock-google-token' && app()->environment('local', 'testing')) {
            $email = $request->input('email', 'mockuser@ubsistore.test');
            $name = $request->input('name', 'Mock Google User');
            $googleId = $request->input('google_id', 'mock-google-id-123456');
        } else {
            // Call Google's tokeninfo API to verify the integrity and decode the ID token
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if (!$response->successful()) {
                Log::error('Google Token Verification Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw ValidationException::withMessages([
                    'id_token' => ['Token Google tidak valid atau telah kedaluwarsa.'],
                ]);
            }

            $payload = $response->json();

            // Optionally verify client ID (audience) if set
            $clientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');
            if ($clientId && isset($payload['aud']) && $payload['aud'] !== $clientId) {
                Log::error('Google Token Client ID Mismatch', [
                    'expected' => $clientId,
                    'actual' => $payload['aud'],
                ]);
                throw ValidationException::withMessages([
                    'id_token' => ['Client ID tidak sesuai.'],
                ]);
            }

            $email = $payload['email'] ?? null;
            $name = $payload['name'] ?? null;
            $googleId = $payload['sub'] ?? null; // 'sub' is Google's unique identifier for the user
        }

        if (empty($email) || empty($googleId)) {
            throw ValidationException::withMessages([
                'id_token' => ['Data email atau Google ID tidak ditemukan di dalam token.'],
            ]);
        }

        // 1. Search for user by google_id
        $user = User::where('google_id', $googleId)->first();

        if (!$user) {
            // 2. Search for user by email to link the Google ID if they registered with email previously
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleId,
                ]);
            } else {
                // 3. Create a new user with Customer role
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'role' => User::ROLE_CUSTOMER,
                    'is_active' => true,
                    'push_notifications_enabled' => true,
                    'email_notifications_enabled' => true,
                    'biometric_login_enabled' => false,
                    'password' => Hash::make(Str::random(16)), // Random strong password since they login via Google
                ]);
            }
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun tidak aktif.'], 403);
        }

        // Ensure the user has an associated shopping cart
        Cart::firstOrCreate(['user_id' => $user->id]);

        // Generate Sanctum access token
        $token = $user->createToken('flutter-token')->plainTextToken;

        return response()->json([
            'message' => 'Login Google berhasil.',
            'token' => $token,
            'user' => $user,
        ]);
    }
}
