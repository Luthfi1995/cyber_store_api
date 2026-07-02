<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ─── Helper: Send Verification Link ──────────────────────────────────────────
    private function sendVerificationEmail(User $user): void
    {
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Catat URL verifikasi di log lokal untuk backup pengetesan
        logger()->info("Verifikasi URL untuk {$user->email}: " . $verificationUrl);

        try {
            Mail::send([], [], function ($message) use ($user, $verificationUrl) {
                $message->to($user->email)
                    ->subject('Verifikasi Email Akun Cyber Store Anda')
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                            <h2 style='color: #0d47a1; margin-bottom: 8px;'>Verifikasi Akun Cyber Store</h2>
                            <p style='color: #475569;'>Halo <strong>{$user->name}</strong>,</p>
                            <p style='color: #475569;'>Terima kasih telah mendaftar di Cyber Store. Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda dan mengaktifkan akun Anda:</p>
                            <div style='text-align: center; margin: 28px 0;'>
                                <a href='{$verificationUrl}' style='background-color: #0d47a1; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;'>Verifikasi Email</a>
                            </div>
                            <p style='color: #64748b; font-size: 13px;'>⏱️ Tautan ini hanya berlaku selama <strong>60 menit</strong>.</p>
                            <p style='color: #64748b; font-size: 13px;'>Jika Anda tidak merasa mendaftar di Cyber Store, abaikan email ini.</p>
                            <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;' />
                            <p style='font-size: 11px; color: #94a3b8;'>Tim Cyber Store</p>
                        </div>
                    ");
            });
        } catch (\Exception $e) {
            logger()->error("Failed to send verification email: " . $e->getMessage());
        }
    }

    // ─── Helper: Generate and Send OTP ──────────────────────────────────────────
    private function generateAndSendOtp(User $user): void
    {
        $otp = sprintf('%06d', mt_rand(0, 999999));

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        logger()->info("OTP Code untuk {$user->email}: " . $otp);

        try {
            Mail::to($user->email)->send(new \App\Mail\OtpVerificationMail($user, $otp));
        } catch (\Exception $e) {
            logger()->error("Failed to send OTP email: " . $e->getMessage());
        }
    }

    // ─── Register ────────────────────────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'address'  => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::create([
            'name'                        => $validated['name'],
            'email'                       => $validated['email'],
            'phone'                       => $validated['phone'] ?? null,
            'password'                    => Hash::make($validated['password']),
            'role'                        => User::ROLE_CUSTOMER,
            'is_active'                   => false,
            'push_notifications_enabled'  => true,
            'email_notifications_enabled' => true,
            'biometric_login_enabled'     => false,
        ]);

        Cart::firstOrCreate(['user_id' => $user->id]);

        // Buat alamat default jika pengguna mengisi field address saat registrasi
        if (!empty($validated['address'])) {
            CustomerAddress::create([
                'user_id'       => $user->id,
                'label'         => 'Rumah',
                'receiver_name' => $user->name,
                'phone'         => $user->phone ?? '-',
                'address'       => $validated['address'],
                'province'      => 'DKI Jakarta',
                'city'          => 'Jakarta Selatan',
                'district'      => 'Pasar Minggu',
                'village'       => 'Warung Jati Barat',
                'postal_code'   => '12540',
                'latitude'      => -6.2910,
                'longitude'     => 106.8440,
                'is_default'    => true,
            ]);
        }

        // Kirim OTP verifikasi
        $this->generateAndSendOtp($user);

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan cek email Anda untuk kode OTP verifikasi.',
            'email'   => $user->email,
        ], 201);
    }

    // ─── Verify OTP ──────────────────────────────────────────────────────────────
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 404);
        }

        if ($user->is_active) {
            return response()->json(['message' => 'Akun sudah aktif.'], 200);
        }

        if (! $user->otp_code || $user->otp_code !== $request->otp) {
            return response()->json(['message' => 'Kode OTP tidak valid.'], 422);
        }

        if (! $user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.'], 422);
        }

        // Aktifkan akun dan hapus OTP
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Akun berhasil diverifikasi! Silakan masuk.',
        ], 200);
    }

    // ─── Resend OTP ──────────────────────────────────────────────────────────────
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 404);
        }

        if ($user->is_active) {
            return response()->json(['message' => 'Akun sudah aktif.'], 200);
        }

        // Cegah spam resend (minimal 60 detik antar permintaan)
        if ($user->otp_expires_at && now()->isBefore($user->otp_expires_at->subMinutes(9))) {
            $secondsLeft = now()->diffInSeconds($user->otp_expires_at->subMinutes(9));
            return response()->json([
                'message' => "Tunggu {$secondsLeft} detik sebelum meminta kode baru.",
            ], 429);
        }

        $this->generateAndSendOtp($user);

        return response()->json([
            'message' => 'Kode verifikasi baru telah dikirim ke email Anda.',
        ], 200);
    }

    // ─── Login ───────────────────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $request->input('email');

        $user = User::where('email', $loginInput)
            ->orWhere('phone', $loginInput)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email/No. Handphone atau password tidak sesuai.'],
            ]);
        }

        if (! $user->is_active) {
            // Kirim OTP baru secara otomatis saat login agar user bisa memverifikasi
            $this->generateAndSendOtp($user);
            return response()->json([
                'message' => 'Akun Anda belum aktif. Silakan lakukan verifikasi menggunakan kode OTP yang dikirim ke email Anda.',
                'requires_otp' => true,
                'email' => $user->email,
            ], 403);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $user->createToken('flutter-token')->plainTextToken,
            'user'    => $user,
        ]);
    }

    // ─── Me ──────────────────────────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['defaultAddress']),
        ]);
    }

    // ─── Logout ──────────────────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    // ─── Forgot Password ─────────────────────────────────────────────────────────
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();

        $defaultPassword = 'bs10k3';

        // Reset password ke default 'bs10k3'
        $user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        try {
            Mail::send([], [], function ($message) use ($user, $defaultPassword) {
                $message->to($user->email)
                    ->subject('Reset Password Akun Cyber Store Anda')
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                            <h2 style='color: #0d47a1; margin-bottom: 8px;'>Reset Password Berhasil</h2>
                            <p style='color: #475569;'>Halo <strong>{$user->name}</strong>,</p>
                            <p style='color: #475569;'>Password akun Anda telah berhasil direset. Silakan masuk menggunakan informasi kredensial berikut:</p>
                            <div style='background-color: #f8fafc; border: 1px dashed #cbd5e1; padding: 16px; border-radius: 8px; margin: 24px 0; text-align: center;'>
                                <div style='color: #64748b; font-size: 14px;'>Password Baru Anda:</div>
                                <div style='color: #0d47a1; font-size: 24px; font-weight: bold; letter-spacing: 1px; margin-top: 8px;'>{$defaultPassword}</div>
                            </div>
                            <p style='color: #475569;'>Demi keamanan akun Anda, silakan segera ubah password ini di halaman profil setelah berhasil masuk.</p>
                            <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;' />
                            <p style='font-size: 11px; color: #94a3b8;'>Tim Cyber Store</p>
                        </div>
                    ");
            });
        } catch (\Exception $e) {
            logger()->error("Failed to send reset password email: " . $e->getMessage());
            return response()->json([
                'message' => 'Password berhasil direset, tetapi gagal mengirim email pemberitahuan ke ' . $user->email,
            ], 500);
        }

        return response()->json([
            'message' => 'Password Anda telah direset. Password baru telah dikirim ke email Anda (' . $user->email . '). Silakan periksa inbox atau folder spam.',
        ]);
    }
}
