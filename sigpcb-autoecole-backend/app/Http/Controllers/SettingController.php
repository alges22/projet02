<?php

namespace App\Http\Controllers;

use App\Services\Help;
use App\Models\Service;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    public function index()
    {
        return $this->successResponse([
            "agrement_amount" => Service::demandeAgrementAmount()
        ]);
    }
}
