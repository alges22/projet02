<?php

namespace App\Http\Controllers;
use App\Services\Api;
use App\Models\Inspecteur;
use App\Models\AnnexeAnatt;
use Illuminate\Http\Request;
use App\Models\InspecteurSalle;
use App\Models\Jurie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JurieController extends ApiController
{
    public function index()
    {
        $jury = Jurie::with(['examinateur', 'examinateur.examinateurCategoriePermis', 'annexe'])
            ->orderBy('id', 'desc')
            ->get();
        return $this->successResponse($jury);
    }
    
}
