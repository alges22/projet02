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
            "permis_num_amount" => Service::permisNumAmount(),
            "renouvellement_amount" => Service::renouvellementAmount(),
            "duplicata_amount" => Service::duplicataAmount(),
            "remplacement_amount" => Service::remplacementAmount(),
            "echange_amount" => Service::echangeAmount(),
            "authenticite_amount" => Service::authenticiteAmount(),
            "permis_international_amount" => Service::permisinternationalAmount(),
            "prorogation_amount" => Service::prorogationAmount(),
            "fedapay"  => [
                'name' => "FedaPay",
                "env" => env('FEDAPAY_ENV', 'sandbox'),
                "public_key" => env("FEDAPAY_PUBLIC_KEY")
            ],
            'app' => [
                'name' => env('APP_NAME'),
                'mode' => env('APP_ENV')
            ]
        ]);
    }
}
