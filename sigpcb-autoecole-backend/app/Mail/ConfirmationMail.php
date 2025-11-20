<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $name;
    public $confirmation_link;
    public $autoecolename;
    public $code;

    public function __construct($name, $confirmation_link,$autoecolename,$code)
    {
        $this->name = $name;
        $this->confirmation_link = $confirmation_link;
        $this->autoecolename = $autoecolename;
        $this->code = $code;
    }

    public function build()
    {
        return $this->view('emails.new-user-welcome');
    }
}
