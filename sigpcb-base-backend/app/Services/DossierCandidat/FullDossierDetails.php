<?php

namespace App\Services\DossierCandidat;

use App\Services\Api;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\DossierCandidat;
use App\Models\Candidat\DossierSession;
use App\Models\CategoriePermis;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        // $responseCategoriePermis = Api::base('GET', "categorie-permis/$categoriePermisId");
        // $categoriePermis = Api::data($responseCategoriePermis);
        try {
            $categoriePermis = CategoriePermis::with(['trancheage', 'extensions', 'permisPrealable'])->findOrFail($categoriePermisId);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse('La catégorie de permis avec l\'ID ' . $categoriePermisId . ' n\'a pas été trouvée.', [], null, 404);
        }
        // Récupérer les informations supplémentaires depuis les autres instances
        // Exemple: Récupérer les informations de l'auto-école depuis l'API anatt-autoecole


        $this->dossier['categorie_permis'] =   $categoriePermis;
        $this->loadAllSessions();
        $this->mapLastSession();
        return $this->dossier;
    }

    // private function mapLastSession()
    // {
    //     $session  = DossierSession::where('dossier_candidat_id', $this->dossier->id)->latest()->first()->toArray();

    //     if ($session) {
    //         $autoEcoleId = $session['auto_ecole_id'];
    //         $responseAutoEcole = Api::autoEcole('GET', "auto-ecoles/$autoEcoleId");
    //         $autoEcole = Api::data($responseAutoEcole);

    //         $candidatNpi = $session['npi'];
    //         $responseCandidat = Api::base('GET', "candidats/$candidatNpi");
    //         $Candidat = Api::data($responseCandidat);


    //         $langueId = $session['langue_id'];
    //         $responseLangue = Api::base('GET', "langues/$langueId");
    //         $Langue = Api::data($responseLangue);

    //         // Combiner les résultats pour renvoyer la réponse complète
    //         $session['auto_ecole'] =  $autoEcole;
    //         $session['langue'] =   $Langue;
    //         $session['candidat'] =    $Candidat;
    //     }
    //     $this->dossier->setAttribute('last_dossier_session', $session);
    // }

    private function loadAllSessions()
    {
        $this->dossier->load('dossierSessions');
    }
}
