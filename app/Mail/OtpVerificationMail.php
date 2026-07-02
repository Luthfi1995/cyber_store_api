<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;

    public function __construct(User $user, string $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Kode OTP Verifikasi Akun UBSI Store Anda')
            ->html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                    <h2 style='color: #0d47a1; margin-bottom: 8px;'>Verifikasi Akun UBSI Store</h2>
                    <p style='color: #475569;'>Halo <strong>{$this->user->name}</strong>,</p>
                    <p style='color: #475569;'>Terima kasih telah mendaftar di UBSI Store. Berikut adalah kode OTP verifikasi akun Anda:</p>
                    <div style='text-align: center; margin: 28px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #0d47a1; background-color: #f1f5f9; padding: 12px 24px; border-radius: 8px; display: inline-block;'>{$this->otp}</span>
                    </div>
                    <p style='color: #64748b; font-size: 13px;'>⏱️ Kode ini hanya berlaku selama <strong>10 menit</strong>.</p>
                    <p style='color: #64748b; font-size: 13px;'>Jangan bagikan kode verifikasi (OTP) ini kepada siapapun.</p>
                    <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;' />
                    <p style='font-size: 11px; color: #94a3b8;'>Tim UBSI Store</p>
                </div>
            ");
    }
}
