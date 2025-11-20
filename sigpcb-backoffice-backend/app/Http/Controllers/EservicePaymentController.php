<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\EservicePayment;

class EservicePaymentController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "read-reporting-management","read-exam-results-management"]);

        $query = EservicePayment::orderByDesc('id');

        // Appliquer les filtres
        $query = $this->applyFilters($query);

        $demandes = $query->paginate(10);

        // Obtient les npi distincts des demandes d'agrément
        $npiCollection = $demandes->filter(function ($demande) {
            return !is_null($demande->npi) && $demande->npi !== '';
        })->pluck('npi')->unique();

        // Obtient les candidats en fonction des valeurs de npi
        $candidats = collect(GetCandidat::get($npiCollection->all()));

        // Associe les informations des candidats aux demandes d'agrément
        $demandes->each(function ($demande) use ($candidats) {
            $demandeur = $candidats->where('npi', $demande->npi)->first();
            $demande->demandeur_info = $demandeur;
        });

        return $this->successResponse($demandes);
    }

    public function applyFilters($query)
    {
        // Filtre par recherche
        if ($titre = request('titre')) {
            $query->where('payment_for',$titre);
        }
        // Filtre par recherche
        if ($search = request('annee')) {
            $query->whereYear('date_payment', $search);
        }

        return $query;
    }

}
