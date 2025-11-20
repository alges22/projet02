<?php

namespace App\Services\Exception;

use Exception;
use Illuminate\Support\Facades\Validator;

class ValidationFailedException extends Exception
{

    protected $response;
    public function jsonResponse()
    {
        return $this->response;
    }

    public function setErrors(\Illuminate\Http\JsonResponse $response)
    {
        $this->response = $response;
    }
}
