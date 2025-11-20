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

class JuryDistributionController extends ProgrammationController
{

    protected $pervague = 16;
    public function __invoke(Request $request)
    {
        $v = Validator::make($request->all(), [
            'annexe_id' => 'required|integer',
            "examen_id" => 'required|integer',
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors());
        }

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
        $compo = new ConduiteCompo([], $annexe_id, []);

        $this->cleanUpOldGenerated($examen->id, $annexe_id);
        //Si le fichier temporaire des vague de composition existe
        # Les données de récupération seront extraites de ce fichier
        # Il contient les vagues pour tout le centre, il était généré dans la méthode generate de cette classe
        if ($compo->canDistribute()) {

            $all = collect([]);
            $assignations =  $compo->getAssignations();
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
            # On récupére l'heure de démarrage
            $date_vague = Carbon::parse($examen->date_conduite)->copy()->setTime(8, 0);

            $groupeParJuries = $all->groupBy('jury_id');
            $date_compo =  $examen->date_conduite;
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

                    //Creation de chaque  candidat
                    foreach ($vague as $key => $data) {
                        $data['conduite_vague_id']  = $vagueInstance->id;
                        JuryCandidat::create($data);
                    }
                    $numero++;
                }
            }
            //Le jour suivant, il faut 8 vagues par jour
            $date_vague = $date_compo->addDays();
        }

        //Il faut libérer le fichier temporaire
        $compo->removeTempFile();
        return $this->successResponse([], "Répartition des jury terminéee avec succès");
    }



    private function cleanUpOldGenerated(int $examen_id, int $annexe_id)
    {

        # On supprime tous les candidats présentés
        $candidats = JuryCandidat::where(
            [
                'examen_id' => $examen_id,
                'annexe_id' => $annexe_id
            ]
        );
        $candidats->delete();
    }
}