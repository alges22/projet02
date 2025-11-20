<?php

namespace App\Http\Controllers;

use App\Compos\Compo;
use App\Compos\ConduiteCompo;
use App\Models\Vague;
use App\Models\Admin\Examen;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\CandidatExamenSalle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ProgrammationController;
use App\Models\ConduiteVague;
use App\Models\JuryCandidat;
use App\Models\Candidat\DossierSession;

class JuryDistributionByVagueController extends ProgrammationController
{
    protected $pervague = 16;

    public function __invoke(Request $request)
    {

        try {
            // Vérifier si on reçoit les nouveaux paramètres (tableau de dossier_session_id)
            if (isset($request[0]) && is_array($request[0])) {
                $dossierSessionIds = $request[0];

                // Récupérer annexe_id et examen_id du premier élément pour compatibilité
                if (!empty($dossierSessionIds) && isset($request[1]) && isset($request[2])) {
                    $annexe_id = $request[1][0]->annexe_id ?? null;
                    $examen_id = $request[2][0]->examen_id ?? null;

                    if (!$annexe_id || !$examen_id) {
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
                $annexe_id = $request->annexe_id;
                $dossierSessionIds = null; // Pas fourni dans l'ancien format
            }

            $examen = Examen::find($examen_id);

            if (!$examen) {
                return $this->errorResponse(
                    "Aucun examen trouvé",
                    statuscode: 422,
                    responsecode: 0
                );
            }

            // Si des dossierSessionIds sont fournis, filtrer pour ne garder que les dossiers qui ont réussi
            if ($dossierSessionIds) {
                $successDossiers = $this->filterSuccessDossierSessions($dossierSessionIds, $annexe_id, $examen_id);
                if (empty($successDossiers)) {
                    return $this->errorResponse("Aucun dossier avec succès trouvé parmi les identifiants fournis");
                }
            }

            $compo = new ConduiteCompo([], $annexe_id, []);

            // $this->cleanUpOldGenerated($examen->id, $annexe_id);

            // Si le fichier temporaire des vagues de composition existe
            if ($compo->canDistribute()) {
                $all = collect([]);
                $assignations = $compo->getAssignations();

                foreach ($assignations as $assignation) {
                    $jury = $assignation['jury'];
                    $groups = $assignation['group'];

                    foreach ($groups as $candidat) {
                        $data = [
                            'npi' => $candidat['npi'],
                            'jury_id' => $jury['id'],
                            'examen_id' => $examen_id,
                            'annexe_id' => $candidat['annexe_id'],
                            'dossier_session_id' => $candidat['id'],
                            'langue_id' => $candidat['langue_id'],
                            'categorie_permis_id' => $candidat['categorie_permis_id'],
                        ];

                        $all->push($data);
                    }
                }

                # On récupère l'heure de démarrage
                $date_vague = Carbon::parse($examen->date_conduite)->copy()->setTime(8, 0);

                $groupeParJuries = $all->groupBy('jury_id');
                $date_compo = $examen->date_conduite;

                foreach ($groupeParJuries as $key => $groupDuJury) {
                    $vagues = $groupDuJury->chunk($this->pervague);

                    $numero = 1;
                    foreach ($vagues as $keyVague => $vague) {
                        $vagueInstance = ConduiteVague::create([
                            'numero' => $numero,
                            'date_compo' => $date_vague,
                            'annexe_anatt_id' => $annexe_id,
                            "examen_id" => $examen_id,
                        ]);

                        // Création de chaque candidat avec vérification d'existence
                        foreach ($vague as $key => $data) {
                            // Vérifier si ce candidat existe déjà pour cet examen et ce centre
                            $existingCandidat = JuryCandidat::where([
                                'npi' => $data['npi'],
                                'examen_id' => $examen_id,
                                'annexe_id' => $annexe_id
                            ])->first();

                            // N'insérer que s'il n'existe pas déjà
                            if (!$existingCandidat) {
                                $data['conduite_vague_id'] = $vagueInstance->id;
                                JuryCandidat::create($data);
                            } else {
                                logger()->info("Candidat avec NPI {$data['npi']} déjà assigné dans cet examen. Ignoré.");
                            }
                        }
                        $numero++;
                    }
                }

                // Le jour suivant, il faut 8 vagues par jour
                $date_vague = $date_compo->addDays();
            } else {
                return $this->errorResponse("Impossible de distribuer les jurys, aucune composition n'a été générée");
            }

            // Il faut libérer le fichier temporaire
            $compo->removeTempFile();
            return $this->successResponse([], "Répartition des jurys terminée avec succès");

        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Filtre les dossiers de session fournis pour ne garder que ceux qui ont réussi
     *
     * @param array $dossierSessionIds
     * @param int $annexeId
     * @param int $examenId
     * @return array
     */
    protected function filterSuccessDossierSessions(array $dossierSessionIds, int $annexeId, int $examenId)
    {
        // Récupérer les dossiers de session qui correspondent aux critères
        $sessions = DossierSession::whereIn('id', $dossierSessionIds)
            ->where([
                'state' => "validate",
                'resultat_code' => "success",
                'closed' => false,
                'examen_id' => $examenId,
                'annexe_id' => $annexeId,
                'abandoned' => false,
            ])
            ->orderBy('created_at', 'desc') // Pour prendre les plus récents d'abord si nécessaire
            ->get();

        // Garder seulement un enregistrement par npi
        $uniqueIds = [];
        $seenNpis = [];

        foreach ($sessions as $session) {
            if (!isset($seenNpis[$session->npi])) {
                $uniqueIds[] = $session->id;
                $seenNpis[$session->npi] = true;
            }
        }

        return $uniqueIds;
    }
    private function cleanUpOldGenerated(int $examen_id, int $annexe_id)
    {
        # On supprime tous les candidats présentés
        $candidats = JuryCandidat::where([
            'examen_id' => $examen_id,
            'annexe_id' => $annexe_id
        ]);
        $candidats->delete();
    }
}
