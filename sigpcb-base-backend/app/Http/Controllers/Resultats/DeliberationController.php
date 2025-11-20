<?php

namespace App\Http\Controllers\Resultats;

use App\Models\Vague;
use App\Services\Api;
use App\Models\Permis;
use Illuminate\Http\Request;
use App\Models\CandidatReponse;
use App\Models\AnnexeResultatState;
use App\Models\CandidatExamenSalle;
use App\Models\Candidat\DossierSession;
use App\Http\Controllers\DataController;

class DeliberationController extends DataController
{
    /**
     * L'instance de requête
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $instance;

    public function admis(Request $request)
    {
        $this->instance = (new DossierSession())->query()->where([
            'state' => "validate",
            'resultat_code' => "success",
            'resultat_conduite' => 'success',
        ]);

        return $this->list($request);
    }

    public function recales(Request $request)
    {
        $this->instance = (new DossierSession())->query()->where('state', 'validate')
            ->where(function ($query) {
                $query->where('resultat_code', 'failed')
                    ->orWhere('resultat_conduite', 'failed');
            });

        return $this->list($request);
    }

    private function list(Request $request)
    {

        if (!is_numeric($request->examen_id)) {
            return $this->errorResponse("Vous devez sélectionner un examen pour voir les résultats");
        }

        if (!is_numeric($request->annexe_id)) {
            return $this->errorResponse("Vous devez sélectionner une annexe pour voir les résultats");
        }
        try {
            $data = [
                'annexe_id' => $request->annexe_id,
                "examen_id" => $request->examen_id
            ];
            if (!$this->annexeResultAreReady($data)) {
                return $this->successResponse(-1, "Les résutalts ne sont pas encore disponibles");
            }
            $this->sortData($request);



            $candidats = $this->getCandidats($this->instance->get());

            /************************** Transformation des suivis ******************************** */
            # Map chaque ligne de Dossier et pour ajouter le candidat et le dossier et les autes champs utilis

            $transformDs = function (DossierSession $ds) use ($candidats) {
                return $this->callScope($ds, $candidats);
            };
            /**************************Fin de la transformation des suivis****************************** */

            $resultats = $this->instance->get()->map($transformDs);

            return $this->successResponse($resultats);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des candidats.', statuscode: 500);
        }
    }

    /**
     * Ajoute les données et informations nécessaires
     *
     * @param DossierSession $ds
     * @param boolean $partial
     */
    private function callScope(DossierSession $ds, array $candidats)
    {
        //Charge les candidats
        $ds->withCandidat($candidats);
        $ds->withCategoriePermis();
        $ds->withAutoEcole();
        # Candidat salle

        return $ds;
    }

    public function publicAttrs(): array
    {
        return
            [
                'id',
                'langue_id',
                'annexe_id',
                'examen_id',
                'npi',
                'state',
                'categorie_permis_id',
                'auto_ecole_id',
                "presence_conduite",
                "resultat_code",
                "resultat_conduite",

            ];
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

    public function filtreAttrs(): array
    {
        return [
            'state', 'auto_ecole_id', 'categorie_permis_id', 'langue_id', 'examen_id', 'type_examen', 'annexe_id', 'examen_id', 'presence', 'resultat_code', 'resultat_conduite', 'presence_conduite'
        ];
    }

    public function toIntegers(): array
    {
        return  ['auto_ecole_id', 'categorie_permis_id', 'langue_id', 'examen_id', 'annexe_id'];
    }


    public function defaultValues(): array
    {
        return [
            "type_examen" => "code-conduite"
        ];
    }

    protected function sortData(Request $request)
    {
        # Si une année  est passé la requête sera fait suivant l'année
        $this->instance = $this->filterIfHasYear($this->instance);

        # Filtrage de la requête
        # Néttoyage des champs
        $filters = $this->getFilters($request);


        # Recherche d'un dossiers via npi
        $search = request('search') ?? request('npi');

        if ($search) {
            $this->instance = $this->instance->where('npi', 'LIKE', "%" . trim($search) . "%");
        }


        # S'il un des champs de filtre est présent, on le fait sinon rien
        if (!empty($filters)) {
            $this->instance = $this->instance->where($filters);
        }
        $this->instance = $this->instance->select($this->publicAttrs());
        return $this->instance;
    }



    protected function annexeResultAreReady(array $data)
    {
        $state = AnnexeResultatState::where($data)->first();


        // Vérifie si un enregistrement AnnexeResultatState correspondant aux données existe et si il est marqué comme prêt.
        if (!is_null($state) && $state->ready) {
            return true;
        }

        // Vérifie si un enregistrement AnnexeResultatState correspondant aux données existe et si il est marqué comme prêt.
        /**
         * Collection $vagues
         */
        $vagues = Vague::where([
            'examen_id' => $data['examen_id'],
            'annexe_anatt_id' => $data['annexe_id'],
        ])->get();

        // On récupère toutes les vagues liées à cet examen et à cette annexe.
        if ($vagues->every(fn ($vague) => $vague->closed)) {
            $data['ready'] = true;
            AnnexeResultatState::create($data);
            return true;
        }
        // Si aucune condition n'est remplie, les résultats ne sont pas encore prêts.
        return false;
    }
}
