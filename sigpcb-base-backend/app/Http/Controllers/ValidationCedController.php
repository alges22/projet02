<?php

namespace App\Http\Controllers;

use App\Models\Candidat\CandidatJustifAbsence;
use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\CategoriePermis;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Validator;

class ValidationCedController extends ApiController
{
    public function __construct()
    {
    }
    public function index(Request $request)
    {
        $per_page =  10;
        $list = $request->get('list');

        if ($request->has('list')) {
            $request->query->set('state', $list);
        }
        $justifications = new CandidatJustifAbsence();


        try {
            //Les clés de filtrage
            $filters = $request->only(['state', 'categorie_permis_id', 'examen_id']);

            ######################### Il faut convertir les données correctements #######################################
            $filters =  collect($filters)->map(function ($qs, $key) {
                //Les entiers
                if (in_array($key, ['categorie_permis_id',  'examen_id'])) {
                    return intval(trim($qs));
                }
                return trim($qs);
            })->filter(function ($qs, $key) {
                //Les entiers supérieurs à 0 au moins
                if (in_array($key, ['categorie_permis_id', 'examen_id'])) {
                    return $qs > 0;
                }
                # On ne s'intéresse par aux restes
                return true;
            })->all();


            //Recherche d'une abscence par npi
            $search = request('search') ?? request('npi');

            if ($search) {
                $justifications = $justifications->where('npi', 'LIKE', "%" . trim($search) . "%");
            }

            /**
             * Filtrage
             */
            if (!empty($filters)) {
                $justifications = $justifications->where($filters);
            }

            # Faire une pagination
            $justifications = $justifications->paginate($per_page);


            $npis = $justifications->map(function ($s) {
                return $s->npi;
            })->join(',');

            $response = Api::anip("GET", "candidats", ['npis' => $npis]);
            $candidats = Api::data($response);

            /************************** Transformation des absences ************************************ */
            # Map chaque ligne de jusitifcation et ajoute le candidat

            $transformJusftif = function (CandidatJustifAbsence $justif) use ($candidats) {
                # Récupération du candidat
                $candidat = collect($candidats)->where(function ($c) use ($justif) {
                    return $c['npi'] === $justif->npi;
                })->first();
                $justif->setAttribute('candidat', $candidat);
                return $this->justifMap($justif, request()->has("partial"));
            };
            /***********************Fin de la transformation des justification***************************** */

            return $this->withPagination($justifications, $transformJusftif);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des justifications', statuscode: 500);
        }
    }

    public function validation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'justif_id' => 'required|integer',
            'state' => 'required|in:validate,rejet',
            'agent_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échoué', $validator->errors(), statuscode: 422);
        }

        if ($request->state === 'rejet') {
            $validator = Validator::make($request->all(), [
                'consignes' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('La validation a échoué', $validator->errors(), statuscode: 422);
            }
        }


        try {
            $justification = CandidatJustifAbsence::findOrFail($request->justif_id);

            if ($justification->state == $request->state) {
                return $this->errorResponse("Cette justification était déjà justifiée ou rejetée ", statuscode: 419);
            }
            // Update the state
            $justification->state = $request->state;


            // Update the 'agent_id' field
            $justification->agent_id = $request->agent_id;


            $oldDs = DossierSession::findOrFail($justification->dossier_session_id);

            ################## Si la justification est validée on crée une nouvelle session #######################
            if ($request->state == 'validate') {
                $ods = $oldDs->toArray();
                $ods['old_ds_justif_id'] = $ods['id']; //ajout de l'ancien id
                $ods['examen_id'] = null;
                $ods['presence'] = null;
                $ods['presence_conduite'] = null;
                unset($ods['id']); //Supprime l'ID pour qu'il soit crée dynamiquement
                unset($ods['created_at']); //Supprime le created_at pour qu'il soit crée dynamiquement
                unset($ods['updated_at']); //Supprime le updated_at pour qu'il soit crée dynamiquement
                $ods['closed'] = true;
                $ods['state'] = "validate";
                DossierSession::create($ods);
            }

            //On ferme le dossier
            $oldDs->closed = true;
            $oldDs->save();


            $justification->save();

            $message = $justification->state == 'rejet' ? "Justification rejetée avec succès" : "Justification validée avec succès";

            $this->informCandidat($justification, $request->consignes);

            return $this->successResponse($justification, $message);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour de la validation de la justification.", statuscode: 500);
        }
    }

    private function justifMap(CandidatJustifAbsence $justif, $partial = true)
    {
        $champsUtils = ["categorie_permis_id", "id"];

        //Dossier session avec unquement les champs utils
        $dossier_session =  DossierSession::find(
            $justif->dossier_session_id,
            $champsUtils
        );
        $justif->setAttribute('dossier_session', $dossier_session);


        # On ajoute le permis
        $permis = CategoriePermis::find($justif->categorie_permis_id, ['id', 'name']);
        $justif->setAttribute('categorie_permis', $permis);

        //renvoie les champs de base
        if ($partial) {
            return $justif;
        }

        return $justif;
    }
}
