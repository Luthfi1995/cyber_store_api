<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_user_registration_generates_and_sends_otp()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@ubsistore.test',
            'phone' => '08123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Registrasi berhasil. Silakan cek email Anda untuk kode OTP verifikasi.',
                'email' => 'johndoe@ubsistore.test',
            ]);

        $user = User::where('email', 'johndoe@ubsistore.test')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_active);
        // OTP sekarang disimpan sebagai bcrypt hash — hanya perlu memastikan tidak null
        $this->assertNotNull($user->otp_code);
        $this->assertNotNull($user->otp_expires_at);

        // Verify OTP is sent via Mail (queue)
        Mail::assertQueued(\App\Mail\OtpVerificationMail::class, function ($mail) {
            return $mail->hasTo('johndoe@ubsistore.test');
        });
    }

    public function test_otp_verification_activates_user()
    {
        $user = User::create([
            'name'           => 'Verification Test',
            'email'          => 'verify@ubsistore.test',
            'password'       => bcrypt('password123'),
            'role'           => User::ROLE_CUSTOMER,
            'is_active'      => false,
            // [TEST] Simpan OTP sebagai hash agar Hash::check() berhasil di AuthController
            'otp_code'       => \Illuminate\Support\Facades\Hash::make('123456'),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/verify-otp', [
            'email' => 'verify@ubsistore.test',
            'otp'   => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Akun berhasil diverifikasi! Silakan masuk.',
            ]);

        $user->refresh();
        $this->assertTrue($user->is_active);
        $this->assertNull($user->otp_code);
        $this->assertNull($user->otp_expires_at);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_login_with_inactive_user_sends_new_otp()
    {
        $user = User::create([
            'name' => 'Inactive Login Test',
            'email' => 'inactive@ubsistore.test',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => false,
            'otp_code' => '000000',
            'otp_expires_at' => now()->subMinutes(1), // expired
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'inactive@ubsistore.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Akun Anda belum aktif. Silakan lakukan verifikasi menggunakan kode OTP yang dikirim ke email Anda.',
                'requires_otp' => true,
                'email' => 'inactive@ubsistore.test',
            ]);

        $user->refresh();
        $this->assertNotNull($user->otp_code);
        $this->assertNotEquals('000000', $user->otp_code);
        $this->assertTrue(now()->isBefore($user->otp_expires_at));
    }
}
