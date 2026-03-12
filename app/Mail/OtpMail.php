<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    public function __construct(
        public int $otp,
        public string $recipientName = 'Pelanggan Manake',
        public int $expiresInMinutes = 5,
    ) {
    }

    public function build()
    {
        return $this->subject('Kode OTP Manake')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'recipientName' => $this->recipientName,
                'expiresInMinutes' => $this->expiresInMinutes,
            ]);
    }
}
