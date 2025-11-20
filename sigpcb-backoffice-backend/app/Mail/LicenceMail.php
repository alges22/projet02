<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $name;
    public $date_fin;

    public function __construct($name,$date_fin)
    {
        $this->name = $name;
        $this->date_fin = $date_fin;
        
    }

    public function build()
    {
        return $this->view('emails.new-licence-autoecole');
    }
}
