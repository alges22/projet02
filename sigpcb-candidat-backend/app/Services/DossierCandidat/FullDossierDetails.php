<?php

namespace App\Services\DossierCandidat;

use App\Services\Api;
use App\Models\DossierCandidat;
use App\Http\Controllers\ApiController;
use App\Models\DossierSession;

class FullDossierDetails extends ApiController
{
    public function __construct(protected  DossierCandidat $dossier)
    {
    }
    public function get()
    {

        if (!$this->dossier) {
            return $this->errorResponse('Aucun résultat trouvé', statuscode: 404);
        }
        $categoriePermisId = $this->dossier['categorie_permis_id'];
        $responseCategoriePermis = Api::base('GET', "categorie-permis/$categoriePermisId");
        $categoriePermis = Api::data($responseCategoriePermis);

        // Récupérer les informations supplémentaires depuis les autres instances
        // Exemple: Récupérer les informations de l'auto-école depuis l'API anatt-autoecole

        $this->dossier['categorie_permis'] =   $categoriePermis;
        $this->loadAllSessions();
        $this->mapLastSession();
        return $this->dossier;
    }
    private function loadAllSessions()
    {
        $this->dossier->load('dossierSessions');
    }
}
