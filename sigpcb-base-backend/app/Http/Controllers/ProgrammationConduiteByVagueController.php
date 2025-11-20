<?php

namespace App\Http\Controllers;

use App\Models\Admin\Jurie;
use App\Models\Admin\Examen;
use App\Models\JuryCandidat;
use Illuminate\Http\Request;
use App\Compos\ConduiteCompo;
use App\Models\CategoriePermis;
use App\Models\Admin\Examinateur;
use App\Models\Admin\AnnexeAnattJurie;
use Illuminate\Support\Facades\Validator;
use App\Programmation\ProgrammationConduite;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Admin\ExaminateurCategoriePermis;
use App\Models\Candidat\DossierSession;

class ProgrammationConduiteByVagueController extends ApiController
{
    public function __construct() {}

    public function generate(Request $request)
    {
        try {
            // Vérifier si on reçoit les nouveaux paramètres (tableau de dossier_session_id)
            if (isset($request[0]) && is_array($request[0])) {
                $dossierSessionIds = $request[0];
                logger()->info($dossierSessionIds);

                // Récupérer annexe_id et examen_id du premier élément pour compatibilité
                if (!empty($dossierSessionIds) && isset($request[1]) && isset($request[2])) {
                    $annexeId = $request[1][0]->annexe_id ?? null;
                    $examen_id = $request[2][0]->examen_id ?? null;

                    if (!$annexeId || !$examen_id) {
                        return $this->errorResponse("Les identifiants d'annexe ou d'examen sont manquants");
                    }
                } else {
                    return $this->errorResponse("Aucun identifiant de dossier de session fourni");
                }
            } else {
                // Ancien format de requête
                $v = Validator::make($request->all(), [
                    'annexe_id' => 'required|integer',
                    "examen_id" => 'required|integer',
                ]);

                if ($v->fails()) {
                    return $this->errorResponse("La validation a échoué", $v->errors());
                }

                $examen_id = $request->examen_id;
                $annexeId = $request->annexe_id;
                $dossierSessionIds = null; // Pas fourni dans l'ancien format
            }

            // Récupération de l'examen
            $examen = Examen::find($examen_id);

            if (!$examen) {
                return $this->errorResponse("Aucun examen trouvé", statuscode: 422, responsecode: 0);
            }

            if ($examen->closed) {
                return $this->errorResponse("Cette session est déjà clôturée.", responsecode: 1);
            }

            // Filtrage des dossiers de session
            if ($dossierSessionIds) {
                // Nouveau format: Filtrer les ids fournis pour ne garder que ceux qui respectent les conditions
                $data = $this->filterDossierSessions($dossierSessionIds, $annexeId, $examen_id);
            } else {
                // Ancien format: Récupérer tous les dossiers qui respectent les conditions
                $data = $this->getDossierSessions($annexeId, $examen_id);
            }

            if (empty($data)) {
                return $this->errorResponse("Aucun candidat trouvé à programmer", responsecode: 2);
            }

            // Vérifier si des associations jury-examinateur existent déjà
            $existingJuries = Jurie::where([
                'annexe_anatt_id' => $annexeId,
                'examen_id' => $examen_id,
            ])->get();

            $juriesGenereated = [];

            // Si des associations jury-examinateur existent déjà, les réutiliser
            if ($existingJuries->isNotEmpty()) {
                $juriesGenereated = $existingJuries->toArray();
                logger()->info("Réutilisation des associations jury-examinateur existantes. Total: " . count($juriesGenereated));
            } else {
                // Sinon, créer de nouvelles associations jury-examinateur
                $JuryParAnnexe = $this->getAnnexeJury($annexeId);
                $ExaminateurParAnnexe = $this->getAnnexeExaminateur($annexeId);

                if (empty($JuryParAnnexe)) {
                    return $this->errorResponse("Aucun jury trouvé pour ce centre", responsecode: 1);
                }

                if (empty($ExaminateurParAnnexe)) {
                    return $this->errorResponse("Aucun examinateur trouvé pour ce centre", responsecode: 3);
                }

                // Vérification des permissions des examinateurs
                $dossiers = collect($data);
                $permis = $dossiers->pluck('categorie_permis_id')->unique();
                $examinteurPermis = [];

                foreach ($ExaminateurParAnnexe as $xmt) {
                    $currentExaminteurPermis = ExaminateurCategoriePermis::where('examinateur_id', $xmt->id)->get();
                    foreach ($currentExaminteurPermis as $ky => $xmtPermis) {
                        $examinteurPermis[] = intval($xmtPermis->categorie_permis_id);
                    }
                }

                $sansExaminteurs = $permis->filter(function ($permisId) use ($examinteurPermis) {
                    return !in_array($permisId, $examinteurPermis);
                })->all();

                if (!empty($sansExaminteurs)) {
                    $pem = collect($sansExaminteurs)->map(function ($id) {
                        return CategoriePermis::find($id);
                    })->pluck('name')->join(', ');
                    return $this->errorResponse("Les candidats du centre ayant pour catégorie de permis: {$pem} ne semblent pas avoir d'examintateurs");
                }

                // Mélange aléatoire des collections
                $JuryParAnnexe->shuffle();
                $ExaminateurParAnnexe->shuffle();

                // Association des jurys et examinateurs
                $associations = [];
                $securiteIndex = 0;
                $count = $ExaminateurParAnnexe->count();

                while ($ExaminateurParAnnexe->isNotEmpty()) {
                    foreach ($JuryParAnnexe as $jury) {
                        if ($ExaminateurParAnnexe->isEmpty()) break;

                        $examinateur = $ExaminateurParAnnexe->shift();
                        $associations[] = [
                            "jury" => $jury,
                            "examinateur" => $examinateur
                        ];
                    }

                    if ($securiteIndex > $count) break;
                    $securiteIndex++;
                }

                if (empty($associations)) {
                    return $this->errorResponse("Aucune assignation de jurie trouvé", responsecode: 4);
                }

                // Création des jurys
                foreach ($associations as $key => $assoc) {
                    $jury = $assoc['jury'];
                    $examinateur = $assoc['examinateur'];
                    $juriesGenereated[] = Jurie::create([
                        "name" => $jury->name,
                        "annexe_jury_id" => $jury->id,
                        "annexe_anatt_id" => $annexeId,
                        "examinateur_id" => $examinateur->id,
                        "examen_id" => $examen_id,
                    ]);
                }

                logger()->info("Création de nouvelles associations jury-examinateur. Total: " . count($juriesGenereated));
            }

            // Application de la logique de composition
            $compo = new ConduiteCompo($data, $annexeId, $juriesGenereated);
            $stats = [];

            if (!$compo->intoTempFile($stats)) {
                return $this->errorResponse("La programmation a échoué");
            }

            return $this->successResponse($stats, "Programmation des jurys terminée avec succès.");
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Filtre les dossiers de session fournis selon les critères spécifiés
     *
     * @param array $dossierSessionIds
     * @param int $annexeId
     * @param int $examenId
     * @return array
     */
    protected function filterDossierSessions(array $dossierSessionIds, int $annexeId, int $examenId)
    {
        // Commencer avec tous les IDs fournis, mais ne garder que ceux qui respectent les conditions
        return DossierSession::whereIn('id', $dossierSessionIds)
            ->where([
                'state' => "validate",
                'resultat_code' => "success",
                'closed' => false,
                'examen_id' => $examenId,
                'annexe_id' => $annexeId,
                'abandoned' => false,
            ])
            ->get()
            ->toArray();
    }

    /**
     * Récupère tous les dossiers de sessions éligibles pour la programmation
     *
     * @param int $annexeId
     * @param int $examenId
     * @return array
     */
    protected function getDossierSessions(int $annexeId, int $examenId)
    {
        return DossierSession::where([
                'state' => "validate",
                'resultat_code' => "success",
                'closed' => false,
                'examen_id' => $examenId,
                'annexe_id' => $annexeId,
                'abandoned' => false,
            ])
            ->get()
            ->toArray();
    }

    public function resultatCode(Request $request)
    {
        if (!$request->has('annexe_id') || !$request->has('examen_id')) {
            return $this->errorResponse("Les paramètres annexe_id et examen_id sont réquis", statuscode: 400);
        }

        $filters = $request->only(['annexe_id', 'examen_id', 'categorie_permis_id']);

        //Récupération des candidats
        $dossiers = $this->getDossierSessions($request->annexe_id, $request->examen_id);


        return  $this->successResponse($dossiers);
    }

    public function vagues(Request $request)
    {
        if (!$request->has('annexe_id') || !$request->has('examen_id')) {
            return $this->errorResponse("Les paramètres annexe_id et examen_id sont réquis", statuscode: 400);
        }
        $getter = new ProgrammationConduite($request->examen_id, $request->annexe_id);
        $vagues = $getter->get($message);
        return $this->successResponse($vagues, $message);
    }

    /**
     * Undocumented function
     *
     * @param [type] $annexeId
     * @return Collection<int,AnnexeAnattJurie>
     */
    protected function getAnnexeJury($annexeId)
    {
        return AnnexeAnattJurie::select(['id', 'name'])
            ->where('annexe_anatt_id', $annexeId)
            ->get();
    }

    protected function getJurie($annexeId, $examen_id)
    {
        return Jurie::select(['id', 'name', 'annexe_anatt_id', 'examinateur_id', 'examen_id', 'annexe_jury_id'])
            ->where('annexe_anatt_id', $annexeId)
            ->where('examen_id', $examen_id)
            ->get()
            ->toArray();
    }
    /**
     *
     * @return Collection<int,Examinateur>
     */
    protected function getAnnexeExaminateur($annexeId)
    {
        return Examinateur::select(['id', 'user_id'])
            ->where('annexe_anatt_id', $annexeId)
            ->get();
    }
}
