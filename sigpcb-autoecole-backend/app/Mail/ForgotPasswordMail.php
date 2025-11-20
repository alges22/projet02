<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $url;

    /**
     * Create a new message instance.
     *
     * @param string $otp
     * @param string $url
     * @return void
     */
    public function __construct($otp, $url)
    {
        $this->otp = $otp;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $urlWithOtp = $this->url . '?token=' . $this->otp;
        return $this->view('emails.forgot_password')
            ->with(['envoie' => $urlWithOtp])
            ->subject('Réinitialisation de mot de passe');
    }
}
