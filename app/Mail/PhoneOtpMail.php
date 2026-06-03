<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PhoneOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $phone,
        public string $recipientName = 'Penyewa Manake',
        public int $expiresInMinutes = 5,
    ) {
    }

    public function build()
    {
        return $this->subject('Kode OTP Verifikasi Telepon - Manake')
            ->view('emails.phone_otp')
            ->with([
                'otp' => $this->otp,
                'phone' => $this->phone,
                'recipientName' => $this->recipientName,
                'expiresInMinutes' => $this->expiresInMinutes,
            ]);
    }
}
