<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttestationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $encryptedPermis;

    /**
     * Create a new message instance.
     *
     * @param string $otp
     * @param string $url
     * @return void
     */
    public function __construct($encryptedPermis)
    {
        $this->encryptedPermis = $encryptedPermis;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $candidatEndpoint = env('CANDIDAT');

        $urlWithOtp = $candidatEndpoint . 'generate-attestation/' . $this->encryptedPermis;
        return $this->view('emails.attestation')
            ->with(['envoie' => $urlWithOtp])
            ->subject('Attestation de Permis');
    }
}
