<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\AutoEcole\Licence;
use App\Http\Controllers\ApiController;


class LicenceController extends ApiController
{

    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "read-licences-management","edit-licences-management"]);

        $query = Licence::with(['autoecole.promoteur', 'autoecole.commune', 'autoecole.departement', 'autoecole.agrement']);
        $query->orderByDesc('id');
        // Appliquer les filtres
        $query = $this->applyFilters($query);

        $ae = $query->paginate(10);
        // on initialise une collection vide pour stocker les npi
        $npiCollection = collect();

        // Parcourir les auto-écoles pour récupérer les licences, les moniteurs, et les NPI
        foreach ($ae as $autoecole) {
            // Ajouter le NPI du promoteur à la collection
            $promoteurNPI = $autoecole->autoecole->promoteur->npi;
            $npiCollection->push($promoteurNPI);
        }

        // Retirer les valeurs nulles ou vides de la collection
        $npiCollection = $npiCollection->filter(function ($npi) {
            return !is_null($npi) && $npi !== '';
        });

        // Retirer les doublons de la collection
        $npiCollection = $npiCollection->unique();

        // Obtient les candidats en fonction des valeurs de npi
        $candidats = collect(GetCandidat::get($npiCollection->all()));

        // Associer les informations des candidats aux auto-écoles
        foreach ($ae as $autoecole) {
            // Associer le promoteur
            $promoteur = $candidats->where('npi', $autoecole->autoecole->promoteur->npi)->first();
            $autoecole->promoteur_info = $promoteur;
        }
        return $this->successResponse($ae);
    }

    public function applyFilters($query)
    {
        // Filtre par département
        if (request()->has('status')) {
            $status = request('status');

            if ($status === 'true') {
                // Statut true : licences dont la date_fin n'est pas dépassée
                $query->where('date_fin', '>=', now()->toDateString());
            } elseif ($status === 'false') {
                // Statut false : licences dont la date_fin est dépassée
                $query->where('date_fin', '<', now()->toDateString());
            }
        }

        if (request()->has('date_debut')) {
            $query = $query->where('date_debut', request('date_debut'));
        }

        if (request()->has('date_fin')) {
            $query = $query->where('date_fin', request('date_fin'));
        }
        // Filtre par recherche
        if (request()->has('search')) {
            $searchTerm = request('search');
            $query->where(function ($query) use ($searchTerm) {
                $query->whereHas('autoecole', function ($autoecoleQuery) use ($searchTerm) {
                    $autoecoleQuery->where('name', 'LIKE', "%$searchTerm%")
                        ->orWhere('num_ifu', 'LIKE', "%$searchTerm%");
                });
            });
        }

        return $query;
    }
}
