<?php

namespace App\Http\Controllers;

use App\Models\AnnexeAnatt;
use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\Candidat\Candidat;
use App\Models\Candidat\DossierSession;
use App\Services\GetCandidat;


class CandidatController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all","read-candidates-menu-access"]);

        $query = Candidat::orderByDesc('id');

        // Appliquer les filtres
        $query = $this->applyFilters($query);

        $demandes = $query->paginate(10);

        // Obtient les npi distincts des demandes d'agrément
        $npiCollection = $demandes->filter(function ($demande) {
            return !is_null($demande->npi) && $demande->npi !== '';
        })->pluck('npi')->unique();

        // Obtient les candidats en fonction des valeurs de npi
        $candidats = collect(GetCandidat::get($npiCollection->all()));

        $demandes->each(function ($demande) use ($candidats) {
            $demandeur = $candidats->where('npi', $demande->npi)->first();
            $demande->candidat_info = $demandeur;
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

    public function fullDetails($dossier_id)
    {

        $data = Api::data(Api::base("GET", "dossier-candidat/{$dossier_id}/full"));

        return $this->successResponse($data);
    }

    public function historics($npi)
    {
        $this->hasAnyPermission(["all","read-candidates-menu-access"]);

        $with = ['dossier', 'autoEcole', 'categoriePermis', 'examen', 'exmaneSalle', 'langue'];
        $candidat = [];
        try {
            $candidat = GetCandidat::findOne($npi);
        } catch (\Throwable $th) {
            return $this->errorResponse("Impossible de récupérer ce candidat");
        }


        $dossierSessions = DossierSession::with($with)->orderByDesc('created_at')->where('npi', $npi)->get()->map(function (DossierSession $dossierSession) use ($candidat) {
            if ($dossierSession->annexe_id) {
                $dossierSession->annexe = AnnexeAnatt::find($dossierSession->annexe_id);
            }
            $dossierSession->withRestriction();
            $dossierSession->withChapitres();
            $dossierSession->withExtension();
            $dossierSession->withPrealable();
            return $dossierSession;
        });

        return $this->successResponse([
            'candidat' => $candidat,
            'historiques' => $dossierSessions,
        ]);
    }

    public function getImages(Request $request)
    {
        try {
            // Récupérer les NPIs envoyés dans la requête sous forme de tableau
            $npis = $request->input('npis'); 

            // Vérifier si le tableau des NPIs est vide
            if (empty($npis) || !is_array($npis)) {
                return $this->errorResponse("Aucun NPI valide fourni", null, null, 400);
            }

            // Tableau pour stocker les résultats
            $result = [];

            // Boucle sur chaque NPI pour récupérer son image
            foreach ($npis as $npi) {
                // Récupérer l'image pour un NPI spécifique
                $image = GetCandidat::getImage([$npi]);

                // Si l'image existe, on l'ajoute au tableau, sinon on met "Sans image"
                $result[] = [
                    'npi' => $npi,
                    'image' => isset($image[0]['image']) ? $image[0]['image'] : 'Sans image',
                ];
            }

            // Retourner la réponse avec les résultats
            return $this->successResponse($result);

        } catch (\Throwable $th) {
            // Log l'erreur et renvoyer une réponse d'erreur générique
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }


}
