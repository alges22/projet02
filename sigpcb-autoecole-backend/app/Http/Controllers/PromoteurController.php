<?php

namespace App\Http\Controllers;

use App\Models\AutoEcole;
use App\Services\GetCandidat;
use Illuminate\Http\Request;

class PromoteurController extends ApiController
{
    public function autoEcoles()
    {
        try {
            if (!auth()->check()) {
                return $this->errorResponse("Vous n'avez pas les autorisations nécessaire pour accéder à cette page", statuscode: 403);
            }
            $promoteurId = auth()->id();
            $aes =  AutoEcole::with(['moniteurs'])->orderBy('created_at')->where('promoteur_id', $promoteurId)->get()->map(fn (AutoEcole $ae) => $ae->annexe());
            return $this->successResponse($aes);
        } catch (\Throwable $th) {

            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la récupération de vos auto-écoles");
        }
    }
}