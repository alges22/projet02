<?php

namespace App\Services\Mail;

use App\Services\Mail\Messager;
use App\Notifications\NotifyCandidat;
use Illuminate\Notifications\Notifiable;

class EmailNotifier
{
    use Notifiable;
    public $email = null;



    public function __construct(public Messager $messager, $data)
    {
        $this->extractEmailIfArray($data)
            ->extractEmailIfObject($data)
            ->extractEmailIfString($data);
    }

    private function extractEmailIfArray($data)
    {
        if (is_array($data)) {
            // Assuming the email is stored in 'email' key
            $this->email = $data['email'] ?? null;
        }
        return $this;
    }

    private function extractEmailIfObject($data)
    {
        if (is_object($data) && isset($data->email)) {
            $this->email = $data->email;
        }
        return $this;
    }

    private function extractEmailIfString($data)
    {
        if (is_string($data)) {
            // Assuming the email is directly passed as the data
            $this->email = $data;
        }
        return $this;
    }

    public function procced()
    {
        //Si le candidat a une email
        if ($this->email) {
            $this->notify(new NotifyCandidat($this->messager));
        }

        return $this;
    }
}
