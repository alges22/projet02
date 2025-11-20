<?php

namespace App\Http\Controllers\Programmation;

use App\Compos\Compo;
use App\Models\Vague;
use App\Models\Admin\Examen;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\AnnexeResultatState;
use App\Models\CandidatExamenSalle;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ProgrammationController;
use App\Models\CandidatQuestion;
use App\Models\CandidatReponse;

class SalleDistributionController extends ProgrammationController
{

    /**
     * Lors que la liste des vagues pour tout le centre est créée
     * Cette méthode est appélée pour récupérer cette liste afin de créer les vagues suivant les salles, le permis et la langue et autres
     * Elle pourra créer la première vague, la deuxième etc
     * Si la liste des vagues dépasse le nombre de vagues autorisé par jour on passe au jour suivant
     * @param Request $request
     */
    public function __invoke(Request $request)
    {
        $v = Validator::make($request->all(), [
            'annexe_id' => 'required|integer',
            "examen_id" => 'required|integer',
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors());
        }

        try {
            $examen_id = $request->examen_id;
            $annexe_id = $request->annexe_id;
            $examen = Examen::find($examen_id);

            if (!$examen) {
                return $this->errorResponse(
                    "Aucun n'examen trouvé",
                    $v->errors(),
                    statuscode: 422,
                    responsecode: 0
                );
            }
            $compo = new Compo([], $annexe_id, $this->getSalles($annexe_id), $examen_id);
            /**
             * En cas de régnération
             */
            $this->cleanUpOldGenerated($examen->id, $annexe_id);
            //Si le fichier temporaire des vague de composition existe
            # Les données de récupération seront extraites de ce fichier
            # Il contient les vagues pour tout le centre, il était généré dans la méthode generate de cette classe
            if ($compo->canGenerateSalle()) {

                # On indique l'heure de démarrage
                $date_heure_vague = Carbon::parse($examen->date_code)->copy()->setTime(8, 0);

                foreach ($compo->getVagues() as  $vaguesData) {
                    $salleId = $vaguesData['salle_compo_id'];
                    $candidats = collect($vaguesData['candidats']);

                    # Ceci pour empêcher de créer les vagues vides
                    if (count($candidats) > 0) {
                        # Prendre le numero de vague
                        $firstCandidat = $candidats->first();
                        $numero = Vague::where([
                            'annexe_anatt_id' => $request->annexe_id,
                            'examen_id' => $examen_id,
                            "salle_compo_id" => $salleId,
                        ])->max("numero") ?? 0;
                        /**
                         * Crée la vague
                         * @var Vague
                         */
                        $vague = Vague::create([
                            'annexe_anatt_id' => $request->annexe_id,
                            'examen_id' => $examen_id,
                            "date_compo" => $date_heure_vague,
                            "numero" => $numero + 1,
                            "salle_compo_id" => $salleId,
                            'status' => "new",
                            'categorie_permis_id' => data_get($firstCandidat, 'categorie_permis_id'),
                        ]);

                        $numeroTable = 1;
                        foreach ($candidats as $candidat) {
                            CandidatExamenSalle::create([
                                "salle_compo_id" => $salleId,
                                "npi" => $candidat['npi'], //
                                "num_table" => $numeroTable, //
                                'vague_id' => $vague->id,
                                'dossier_session_id' =>  $candidat['id'],
                                'examen_id' => $examen_id,
                                'annexe_id' => $annexe_id,
                                'langue_id' => $candidat['langue_id'],
                                'categorie_permis_id' => $candidat['categorie_permis_id'],
                                'dossier_candidat_id' => $candidat['dossier_candidat_id']
                            ]);

                            $numeroTable++;
                        }
                    }
                }
            }
            //Il faut libérer le fichier temporaire
            $compo->removeTempFile();
            return $this->successResponse([], "Répartition en salle effectuée avec succès ");
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la distribution des candidats en salle');
        }
    }


    private function cleanUpOldGenerated(int $examen_id, int $annexe_id)
    {
        # S'il y avait des vagues pour ce centre, pour cet examen, cela suppose que c'est une régénération de programmation
        # On supprime tous les candidats présentés
        $candidats = CandidatExamenSalle::where(
            [
                'examen_id' => $examen_id,
                'annexe_id' => $annexe_id
            ]
        );

        foreach ($candidats->get() as $key => $c) {
            CandidatQuestion::where('candidat_salle_id', $c->id)->delete();
            CandidatReponse::where('candidat_salle_id', $c->id)->delete();
        }

        $dss = DossierSession::where([
            'examen_id' => $examen_id,
            'annexe_id' => $annexe_id,
            'type_examen' => "code-conduite"
        ])->get();

        foreach ($dss as $key => $ds) {
            $ds->presence = null;
            $ds->presence_conduite = null;
            $ds->resultat_code = null;
            $ds->resultat_conduite = null;
            $ds->save();
        }

        AnnexeResultatState::where([
            'examen_id' => $examen_id,
            'annexe_id' => $annexe_id
        ])->delete();



        $candidats->delete();

        # On supprime toutes les vagues
        Vague::where([
            'annexe_anatt_id' =>  $annexe_id,
            'examen_id' => $examen_id,
        ])->delete();
    }
}
