<?php

namespace App\Services\Exception;

use Exception;

class ImportationException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
