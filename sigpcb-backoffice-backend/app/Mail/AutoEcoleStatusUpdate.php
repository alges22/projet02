<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoEcoleStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $status;
    public $motif;

    public function __construct($status,$motif)
    {
        $this->status = $status;
        $this->motif = $motif;
        
    }

    public function build()
    {
        $messages = $this->status ? 'Félicitations, votre auto-école a été activée.' : 'Votre auto-école a été désactivée.';
        
        if (!$this->status) {
            $messages .= ' Motif : ' . $this->motif;
        }
    
        return $this->view('emails.update-autoecole', ['messages' => $messages]);
    }
}
