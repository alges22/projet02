<?php

namespace App\Http\Controllers;

use App\Services\Api;
use App\Services\Help;
use Illuminate\Http\Request;
use App\Models\SuiviCandidat;
use App\Models\DossierSession;
use App\Http\Controllers\DataController;
use App\Services\GetCandidat;
use Illuminate\Support\Facades\Validator;

class DossierSessionController extends DataController
{
    public function index(Request $request)
    {
        try {


            $filters = $request->all();
            $filters['auto_ecole_id'] = Help::autoEcoleId();

            if (!$request->has('closed')) {
                $filters['closed'] = false;
            }

            $ds =  DossierSession::filter($filters);

            $candidats = $this->getCandidats($ds->get());

            /************************** Transformation des suivis ******************************** */
            # Map chaque ligne de Dossier et pour ajouter le candidat et le dossier et les autes champs utilis

            $transformDs = function (DossierSession $ds) use ($candidats) {
                return $this->callMap($ds, $candidats);
            };
            /**************************Fin de la transformation des suivis****************************** */

            return $this->withPagination($ds->paginate($this->getPerpage()), $transformDs);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des dossiers de suivi des candidats.', statuscode: 500);
        }
    }

    /**
     * Ajoute les données et informations nécessaires
     *
     * @param DossierSession $ds
     * @param boolean $partial
     */
    protected function callMap(DossierSession $ds, array $candidats)
    {
        $ds->withCandidat($candidats);
        $ds->withCategoriePermis();
        $ds->withDossier();
        $ds->withAutoEcole();
        $ds->withLangue();
        $ds->withAnnexe();
        $ds->withExamen();
        return $ds;
    }

    /**
     * Récupération d'un dossier
     *
     * @param int $dossier_session_id
     */
    public function show($dossier_session_id)
    {
        $ds = DossierSession::find($dossier_session_id);

        if (!$ds) {
            return $this->errorResponse("Dossier session non trouvé", statuscode: 404);
        }
        # Ceci à cause de la fonction callMap
        $candidats[0] = GetCandidat::findOne($ds->npi);

        try {
            return $this->successResponse($this->callMap($ds, $candidats));
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite");
        }
    }



    public function replaceWiths(): array
    {
        return [
            "list" => "state",
            "cat_permis_id" => "categorie_permis_id",
            "dsc_id" => "dossier_candidat_id",
            "lang_id" => "langue_id",
            "exam_id" => "examen_id",
            "aecole_id" => "auto_ecole_id",
        ];
    }



    public function suivisCandidats(Request $request)
    {

        $filters = $request->all();
        $filters['auto_ecole_id'] = Help::autoEcoleId();

        $list = $request->get('state', $request->get('list'));
        if ($list === 'rejet') {
            # Indique de prendre les rejets parents
            $filters["state"] = "rejet";
            $filters["old_ds_rejet_id"] = null;
        }

        if (!$request->has('closed')) {
            $filters['closed'] = false;
        }

        $ds =  DossierSession::filter($filters);

        $candidats = $this->getCandidats($ds->get());

        /**************************Transformation du dossier session ****************** */
        $transformDs = function (DossierSession $ds) use ($candidats) {

            $ds = $this->callMap($ds, $candidats);
            $ds->withChapitres();
            return $ds->withDateSuivi();
        };
        /**************************Fin de la transformation des suivis****************************** */

        return $this->withPagination($ds->paginate($this->getPerpage()), $transformDs);
    }



    public function rejets(Request $request)
    {
        $request->merge([
            'state' => "rejet"
        ]);

        return $this->index($request);
    }


    public function updateStateSuiviCandidat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'state' => "required|in:init,pending,validate,rejet",
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué", $validator->errors(), statuscode: 422);
            }

            if (!$request->has('suivi_id')) {
                $suiviCandidat = SuiviCandidat::where('dossier_session_id', $request->dossier_session_id)->first();
            } else {
                $suiviCandidat = SuiviCandidat::find($request->suivi_id);
            }

            if (!$suiviCandidat) {
                return $this->errorResponse('Le suivi candidat pour ce dossier est introuvable');
            }
            $suiviCandidat->update([
                'state' => $request->get('state'),
            ]);

            return $this->successResponse($suiviCandidat);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur s\'est produite lors de la mise à jour de l\'état du suivi.', 500);
        }
    }

    public function  fullDossierSession($id)
    {
        try {
            $response = Api::base('GET', "dossier-sessions/{$id}/full-details");
            $session = Api::data($response);
            return $this->successResponse($session);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur s\'est produite lors de la mise à jour de l\'état du suivi.', 500);
        }
    }
}
