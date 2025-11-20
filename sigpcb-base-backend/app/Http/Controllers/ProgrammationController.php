<?php

namespace App\Http\Controllers;

use App\Services\Exceptions\ProgrammationException;
use App\Compos\Compo;
use App\Models\Vague;
use App\Models\SalleCompo;
use App\Models\Admin\Examen;
use Illuminate\Http\Request;

use App\Programmation\Programmation;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Validator;

class ProgrammationController extends ApiController
{

    public function __construct() {}

    /**
     * Cette méthode est appélé pour créer juste les vagues suivant le centre.
     * Elle crée les vagues pour tout le centre et les mets dans un fichier temporaire.
     * Ce fichier sera utilisé dans la méthode distributeIntoSalle de cette classe
     * Pour faire la répartion des candidats dans les salles
     *
     * Après la répartion dans les salles la méthode vagues est appélée pour renvoyer maintenant la liste des vagues
     * Les méthodes sont appélées les unes après les autres
     *
     * @param Request $request
     */
    public function generate(Request $request)
    {
        try {
            /**
             * Les programmations sont générées suivant l'examen et l'annexe
             */
            $v = Validator::make($request->all(), [
                'annexe_id' => 'required|integer',
                "examen_id" => 'required|integer',
            ]);

            if ($v->fails()) {
                return $this->errorResponse("La validation a échoué", $v->errors());
            }
            $examen_id = $request->examen_id;

            $annexeId  = $request->annexe_id;

            # Récupération des salles de l'annexe
            $salles = $this->getSalles($annexeId);

            /**
             * Récupération de l'examen
             * @var Examen
             */
            $examen = Examen::find($examen_id);

            if (!$examen) {
                return $this->errorResponse("L'examen sélectionné n'existe pas ou a été supprimé", $v->errors(), statuscode: 422, responsecode: 0);
            }


            # On cherche une vague qui est déjà utilisée (en cours, terminé etc)
            $existingVague = Vague::where(
                [
                    'examen_id' => $examen_id,
                    "annexe_anatt_id" => $annexeId
                ]
            )->where('status', '!=', 'new')->first();

            # Si une vague occupée existe, on ne peut plus relancer
            if ($existingVague) {
                return $this->errorResponse("Impossible de générer la programmation. Une composition passée ou recente est associée à cet examen.", $v->errors(), statuscode: 419, responsecode: 0);
            }

            $examen = $examen->withDateCode();

            if (empty($salles)) {
                return $this->errorResponse("Aucune salle trouvée pour ce centre", responsecode: 1, statuscode: 404);
            }

            # On récupère maintenant les dossiers pour ce centre, pour ce cet examen
            # Seul certains champs nécessaire sont pris
            $data = $this->getDossierSessions($annexeId, $examen_id);


            if (empty($data)) {
                return $this->errorResponse("Aucun candidat trouvé à programmer", responsecode: 2);
            }


            # La classe compo gère la logique de programmation
            $compo = new Compo($data, $annexeId, $salles, $examen_id);        //Contient par exemple les informations sur le nombre de vague généré pour le centre
            $stats = [];

            # On écrit dans un fichier temporaire les vagues pour le centre c'est la première étape
            # Pour  récupérer maintenant la liste des vagues, on le fait à travers ce fichier
            # Si la récupération des vagues était faite le code sera long et lent, et la gestion des erreurs sera problémation
            # S'il y a une erreur ici le fichier n'est pas écrit ou même si c'est écrit si l'agent clique sur programmation, il est supprimé
            # Le fichier est supprimé après récupération des vagues
            $tempFileWroten = $compo->intoTempFile($stats);
            if (!$tempFileWroten) {
                $stats['total'] = 0;
            }
            return $this->successResponse($stats, "Génération de vague effectuée avec succès");
        } catch (ProgrammationException $th) {
            logger()->error($th); //throw $th;
            return $this->errorResponse($th->getMessage(), statuscode: 500);
        } catch (\Throwable $th) {
            logger()->error($th); //throw $th;
            return $this->errorResponse("Une erreur s'est produite lors de la génération de la programmation", statuscode: 500);
        }
    }

    /**
     * Une fois la distribution dans les salles est terminéé
     * Cette api renvoie maintenant la liste des vagues avec les informations normales
     * Permis, Candidat,vagues salles et autres
     *
     * @param Request $request
     */
    public function vagues(Request $request)
    {
        try {
            if (!$request->has('annexe_id') || !$request->has('examen_id')) {
                return $this->errorResponse("Les paramètres annexe_id et examen_id sont réquis", statuscode: 400);
            }
            $getter = new Programmation($request->examen_id, $request->annexe_id);
            $vagues = $getter->get($message);
            return $this->successResponse($vagues, $message);
        } catch (\Throwable $th) {
            logger()->error($th); //throw $th;
            return $this->errorResponse("Une erreur s'est produite lors de la récupération de la programmation", statuscode: 500);
        }
    }


    public function statistiques(Request $request)
    {
        try {
            if (!$request->has('annexe_id') || !$request->has('examen_id')) {
                return $this->errorResponse("Les paramètres annexe_id et examen_id sont réquis", statuscode: 400);
            }

            $filters = $request->only(['annexe_id', 'examen_id', 'categorie_permis_id']);
            # Uniquement les candidats
            $filters['state'] = "validate";

            //Récupération des candidats
            $stats['total'] = DossierSession::presentes($filters)->count();

            $stats["vague_count"] = Vague::where([
                'examen_id' => $request->examen_id,
                'annexe_anatt_id' => $request->annexe_id,
            ])->count();

            return  $this->successResponse($stats);
        } catch (\Throwable $th) {
            logger()->error($th); //throw $th;
            return $this->errorResponse("Une erreur s'est produite lors de la récupération des statistiques", statuscode: 500);
        }
    }
    private function getDossierSessions($annexeId, $examen_id)
    {
        return  DossierSession::presentes(["annexe_id" => $annexeId, 'examen_id' => $examen_id])
            ->get(['id', 'categorie_permis_id', 'langue_id', 'npi', 'examen_id', 'annexe_id', 'type_examen', 'closed', 'dossier_candidat_id'])
            ->toArray();
    }
    protected function getSalles($annexId)
    {
        return SalleCompo::select(['id', 'contenance', 'name', 'annexe_anatt_id'])
            ->where('annexe_anatt_id', $annexId)
            ->get()
            ->toArray();
    }
}
