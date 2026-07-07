<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'                       => ['required', 'string', 'max:100'],
            'email'                       => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'phone'                       => ['nullable', 'string', 'max:30'],
            'address'                     => ['nullable', 'string'],
            'current_password'            => ['nullable', 'string'],
            'password'                    => ['nullable', 'string', 'min:8', 'confirmed'],
            'push_notifications_enabled'  => ['sometimes', 'boolean'],
            'email_notifications_enabled' => ['sometimes', 'boolean'],
            'biometric_login_enabled'     => ['sometimes', 'boolean'],
            'photo'                       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // [SECURITY] Jika ingin ganti password, wajib verifikasi password lama
        if (!empty($validated['password'])) {
            if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Password saat ini tidak sesuai.',
                    'errors'  => ['current_password' => ['Password saat ini tidak sesuai.']],
                ], 422);
            }
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        unset($validated['current_password']);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $validated['photo'] = $request->file('photo')->store('users', 'public');
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $user->fresh(),
        ]);
    }
}
