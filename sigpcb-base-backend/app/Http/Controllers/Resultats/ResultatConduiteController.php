<?php

namespace App\Http\Controllers\Resultats;

use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\Candidat\DossierSession;
use App\Http\Controllers\DataController;
use App\Models\CandidatExamenSalle;
use App\Models\CandidatReponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ResultatConduiteController extends DataController
{


    /**
     * L'instance de requête
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $instance;
    public function __construct()
    {
        $this->instance = (new DossierSession())->query()->where([
            'state' => "validate",
            "resultat_code" => "success",
        ]);
        //Filtre les cossiers non fermés par défaut
        $this->closedOrNot();

        # Faire un certain
        $this->filterByDefault();
    }
    public function index(Request $request)

    {
        if (!is_numeric($request->examen_id)) {
            return $this->errorResponse("Vous devez sélectionner un examen pour voir les résultats");
        }

        if (!is_numeric($request->annexe_id)) {
            return $this->errorResponse("Vous devez sélectionner une annexe pour voir les résultats");
        }

        try {

            $this->sortData($request);

            $candidats = $this->getCandidats($this->instance->get());

            /************************** Transformation des suivis ******************************** */
            # Map chaque ligne de Dossier et pour ajouter le candidat et le dossier et les autes champs utilis

            $transformDs = function (DossierSession $ds) use ($candidats) {
                return $this->callScope($ds, $candidats);
            };
            /**************************Fin de la transformation des suivis****************************** */

            $resultats = $this->instance->get()->map($transformDs);

            $dateInsance = Carbon::parse();
            # Groupé par jour
            $resultats = $resultats->groupBy(function ($candidat) use ($dateInsance) {
                $compo = $candidat->compo_code;
                $date = $dateInsance->parse($compo->answers_at)->format('Y-m-d');
                return $date;
            });

            $resultats = $resultats->map(function (Collection $group, $dateCompo) use ($dateInsance) {
                $permisGroup = $group->groupBy('categorie_permis_id');
                return [
                    "date" => $dateInsance->format("l d M Y"),
                    "permis" => $permisGroup->values()
                ];
            });

            $resultats = $resultats->values();
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
        $cs = CandidatExamenSalle::where('dossier_session_id', $ds->id)->first(['id']);

        $candidatResponse = new CandidatReponse([
            'id' => $ds->id,
            "answers_at" => now()->format("Y-m-d H:i:s"),
        ]);
        $ds->setAttribute('compo_code', $candidatResponse);

        if ($ds->resultat_conduite == "success") {
            $status = "Admis(e)";
        } else {
            if ($ds->presence_conduite === 'abscent') {
                $status = "Absent (e)";
            } else {
                $status = "Recalé (e)";
            }
        }

        $ds->setAttribute("status", $status);
        return $ds;
    }


    public function show($dossier_session_id)
    {
        $ds = DossierSession::find($dossier_session_id);

        if (!$ds) {
            return $this->errorResponse("Dossier session non trouvé", statuscode: 404);
        }
        # Ceci à cause de la fonction callScope
        $candidats[0] = Api::data(Api::anip('GET', 'candidats/' . $ds->npi));

        try {
            return $this->successResponse($this->callScope($ds, $candidats));
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite");
        }
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
            'state', 'auto_ecole_id', 'categorie_permis_id', 'langue_id', 'examen_id', 'annexe_id', 'examen_id', 'presence_conduite', 'resultat_conduite',
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



    private function sortData(Request $request)
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
        # Si resutat conduite vaut null c'est qu'on ne veut faire aucun filtre
        if (request('resultat_conduite')) {
            if (!in_array(request('resultat_conduite'), ['success', 'failed'])) {
                unset($filters['resultat_conduite']);
            }
        }

        # S'il un des champs de filtre est présent, on le fait sinon rien
        if (!empty($filters)) {
            $this->instance = $this->instance->where($filters);
        }
        $this->instance = $this->instance->select($this->publicAttrs());
        return $this->instance;
    }



    private function closedOrNot()
    {
    }

    private function filterByDefault()
    {
        $filters = [];

        if (request()->has('annexe_id')) {
            $filters['annexe_id'] = intval(request('annexe_id'));
        }

        if (request()->has('examen_id')) {
            $filters['examen_id'] = intval(request('examen_id'));
        }

        if (!empty($filters)) {
            $this->instance = $this->instance->where($filters);
        }
    }
}
