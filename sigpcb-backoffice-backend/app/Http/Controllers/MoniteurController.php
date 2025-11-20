<?php

namespace App\Http\Controllers;

use App\Models\Moniteur;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Http\Controllers\ApiController;

class MoniteurController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all","read-monitor-recruitment","edit-monitor-recruitment"]);

        $query = Moniteur::orderByDesc('id');
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
        if ($search = request('search')) {
            $query->where('npi', 'LIKE', "%$search%");
        }
        return $query;
    }

}
