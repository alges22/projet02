<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\AutoEcole\SuiviCandidat;
use App\Models\Candidat\DossierSession;
use App\Http\Controllers\DataController;
use App\Models\Candidat\Candidat as User;
use App\Models\Candidat\DossierCandidat;
use App\Models\Candidat\ParcoursSuivi;
use App\Services\Anip;
use App\Services\Api;
use App\Services\DossierCandidat\FullDossierDetails;
use App\Services\Mail\EmailNotifier;
use App\Services\Mail\Messager;
use Illuminate\Support\Facades\Validator;

class DossierSessionController extends DataController
{
    public function index(Request $request)
    {
        try {

            $filters = $request->all();
            if (!$request->has('closed')) {
                $filters['closed'] = false;
            }

            $query =  DossierSession::filter($filters);
            $candidats = $this->getCandidats($query->get());

            /************************** Transformation des suivis ******************************** */
            # Map chaque ligne de Dossier et pour ajouter le candidat et le dossier et les autes champs utilis

            $transformDs = function (DossierSession $ds) use ($candidats) {
                return $this->callScope($ds, $candidats);
            };
            /**************************Fin de la transformation des suivis****************************** */

            return $this->withPagination($query->paginate($this->getPerpage()), $transformDs);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des dossiers de suivi des candidats.', statuscode: 500);
        }
    }

    public function updatStateSuiviCandidat(Request $request)
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
        $ds->withDossier();
        $ds->withAutoEcole();
        $ds->withCategoriePermis();
        $ds->withLangue();
        $ds->withAnnexe();
        $ds->withExamen();
        $ds->withSalle();
        $ds->withVague();
        $ds->withVagueConduite();
        $ds->withRestriction();
        $ds->withExtension();
        $ds->withPrealable();
        $ds->withConduiteJury();
        return $ds;
    }


    public function updateStateSuiviCandidat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'state' => "required|in:init,pending,validate,rejet",
                "dossier_session_id" => "required|integer"
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué", $validator->errors(), statuscode: 422);
            }

            $ds = DossierSession::find($request->dossier_session_id);

            if (!$ds) {
                return $this->errorResponse("Le dossier du candiat est introuvable");
            }

            $suivi = SuiviCandidat::where('dossier_candidat_id', $ds->dossier_candidat_id)->first();

            if (!$suivi) {
                return $this->errorResponse("Le suivi du candiat est introuvable");
            }

            $ds->update(['state' => $request->state]);
            $suivi->update(['updated_at' => now()]);

            return $this->successResponse($ds);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur s\'est produite lors de la mise à jour de l\'état du suivi.', 500);
        }
    }

    public function show($dossier_session_id)
    {
        $ds = DossierSession::find($dossier_session_id);

        if (!$ds) {
            return $this->errorResponse("Dossier session non trouvé", statuscode: 404);
        }
        # Ceci à cause de la fonction callScope
        $candidats[0] = GetCandidat::findOne($ds->npi);

        try {
            return $this->successResponse($this->callScope($ds, $candidats));
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

    public function filtreAttrs(): array
    {
        return [
            'state',
            'auto_ecole_id',
            'categorie_permis_id',
            'langue_id',
            'examen_id',
            'type_examen',
            'annexe_id',
            'examen_id',
            'presence',
            'closed',
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

    public function suivisCandidats(Request $request)
    {
        $filters = $request->all();
        $list = $request->get('state');
        // Vérifier si 'closed' est présent dans la requête
        if (!$request->has('closed')) {
            $filters['closed'] = false;
        }
        // Filtrer par état
        if ($list === 'rejet') {
            $filters["state"] = "rejet";
            $filters["closed"] = false;
        }

        // Vérifier si annexe_anatt_id est présent et l'ajouter au filtre
        if ($request->has('annexe_anatt_id')) {
            $filters['annexe_id'] = $request->get('annexe_anatt_id');
        }

        // Appliquer les filtres à la requête
        $query = DossierSession::filter($filters);

        // Récupérer les candidats
        $candidats = $this->getCandidats($query->get());

        /************************** Transformation du dossier session ****************** */
        $transformDs = function (DossierSession $ds) use ($candidats) {
            $ds = $this->callScope($ds, $candidats);
            $ds->withChapitres();
            return $ds->withDateSuivi();
        };
        /************************** Fin de la transformation des suivis ****************************** */

        // Retourner les résultats paginés
        return $this->withPagination($query->paginate($this->getPerpage()), $transformDs);
    }

    public function rejets(Request $request)
    {
        $request->merge([
            'list' => "rejet"
        ]);

        return $this->index($request);
    }

    public function updateStateCed(Request $request)
    {
        $v = Validator::make($request->all(), [
            'dossier_session_id' => 'required|exists:dossier_sessions,id',
            'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
            'state' => 'required|in:validate,rejet'
        ]);
        if ($v->fails()) {
            return  $this->errorResponse("La validation a échoué", $v->errors());
        }
        try {
            $dossier_session_id = $request->input('dossier_session_id');
            $dossier_candidat_id = $request->input('dossier_candidat_id');
            $agent_id = $request->input('agent_id');
            // Vérifier si le dossier candidat existe avec l'ID donné
            $dossier = DossierCandidat::findOrFail($dossier_candidat_id);
            $dossiersession = DossierSession::findOrFail($dossier_session_id);

            $npi = $dossier->npi;
            $candidat_id = $dossier->candidat_id;
            $categorie_permis_id = $dossier->categorie_permis_id;
            $date_soumission = now();
            // Mettre à jour le champ "state" du dossier avec la valeur de la variable $state
            $state = $request->input('state');

            // Vérifier si le dossier a été rejeté (state = 'rejet')
            if ($state === 'rejet') {
                $motif_rejet = $request->input('motif');
                $consignes = $request->input('consignes');
                $dossiersession->state = $state;
                $dossiersession->save();

                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $npi;
                $parcoursSuivi->slug = "validation-anatt-failed";
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $candidat_id;
                $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                $parcoursSuivi->message = "Votre dossier a été rejeté. Motif : " . $motif_rejet . " Consignes à suivre : " . $consignes;
                $parcoursSuivi->agent_id = $agent_id;
                $parcoursSuivi->bouton = json_encode(['bouton' => 'Rejet', 'status' => '1']);
                $parcoursSuivi->dossier_session_id = $dossier_session_id;
                $parcoursSuivi->date_action = $date_soumission;
                $parcoursSuivi->save();
            } else {
                // Bloc pour la validation réussie (convocation)
                $dossiersession->state = $state;
                $dossiersession->save();

                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $npi;
                $parcoursSuivi->slug = "validation-anatt-success";
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $candidat_id;
                $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                $parcoursSuivi->message = "Votre dossier a été validé par l'ANaTT, votre convocation pour la composition de l'épreuve du code vous sera envoyée dans les prochains jours";
                $parcoursSuivi->agent_id = $agent_id;
                $parcoursSuivi->bouton = json_encode(['bouton' => 'Convocation', 'status' => '0']);
                $parcoursSuivi->dossier_session_id = $dossier_session_id;
                $parcoursSuivi->date_action = $date_soumission;
                $parcoursSuivi->save();
            }

            return $this->successResponse($dossier, 'Le statut du dossier candidat a été mis à jour avec succès.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            logger()->error($e);
            return $this->errorResponse('Dossier candidat non trouvé.', null, null, 422);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du dossier candidat.', null, null, 500);
        }
    }
    public function updateState(Request $request)
    {
        try {

            // Vérifier si le dossier candidat existe avec l'ID donné

            $validator = Validator::make(
                $request->all(),
                [
                    'state' => "required|in:init,pending,validate,rejet,payment",
                    "id" => "required|exists:dossier_sessions,id"
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('La validation a échoué', $validator->errors(), statuscode: 422);
            }
            $id = $request->input('id');
            $session = DossierSession::findOrFail($id);

            // Récupérer le champ "state" du dossier
            $state = $request->input('state');


            // Mettre à jour le champ "state" du dossier avec la valeur de la variable $state
            $session->state = $state;

            $candidat = Anip::get($session->npi);

            $stateMessage = "";
            // Mettre à jour le champ "bouton_paiement" en fonction du state
            if ($state === 'pending') {
                $session->bouton_paiement = 1;
                $stateMessage = "Félicitations votre suivi a été effectué, vous pouvez passer au paiement";
            } elseif ($state === 'init') {
                $session->bouton_paiement = 0;
                $stateMessage = "Mise en attente";
            } elseif ($state === 'validate') {
                $session->bouton_paiement = -1;
                $state = 'validate';
            } else {
                // Si le state ne correspond à aucun des cas ci-dessus, vous pouvez gérer une erreur ou une valeur par défaut ici.
                // Par exemple, retourner une réponse d'erreur ou attribuer une valeur par défaut.
                // Dans cet exemple, je vais simplement attribuer la valeur 0.
                $session->bouton_paiement = 0;
                $session->state = $state; //rejet ou payment
            }


            $success =  $session->save();

            if ($success) {
                $messageBuilder = (new Messager())
                    ->subject('Statut de validation de suivi')
                    ->greeting("Bonjour {$candidat['prenoms']}")
                    ->headline("Statut de votre dossier")
                    ->introParagraph($stateMessage)
                    ->setAction('Me connecter', env('FRONTEND_URL') . '/connexion')
                    ->lastParagraph("En cas d'erreur vous pouvez rapprocher de votre auto-école")
                    ->goodbye('Merci et bonne chance !')
                    ->footer();

                (new EmailNotifier($messageBuilder, $candidat))->procced();
            }
            $session['candidat'] = $candidat;
            return $this->successResponse($session, 'Le statut du dossier candidat a été mis à jour avec succès.');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du dossier candidat.', null, null, 500);
        }
    }

    public function fullDossier($dossier_id)
    {
        try {
            // Utiliser la méthode "find" au lieu de "findOrFail"
            $dossier = DossierCandidat::find($dossier_id);

            if (!$dossier) {
                return $this->errorResponse('Aucun résultat trouvé', statuscode: 404);
            }
            $resultat = (new FullDossierDetails($dossier))->get();
            return $this->successResponse($resultat);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération du dossier du candidat.', statuscode: 500);
        }
    }

    public function showDossier($id)
    {
        try {
            $dossier = DossierCandidat::findOrFail($id);
            //On prend la dernière session
            return $this->successResponse($dossier);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function resultatCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dossier_session_ids' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("La validation a échoué", $validator->errors());
        }

        try {
            $dossierSessionIds = $request->input('dossier_session_ids');

            foreach ($dossierSessionIds as $data) {
                $dossierSessionId = $data['dossier_session_id'];
                $bonnesReponsesCount =  $data['corrects'];
                $count =  $data['count'];
                $finalNote = $bonnesReponsesCount . " / " . $count;
                // Vérifier si le dossier session existe
                $dossierSession = DossierSession::findOrFail($dossierSessionId);

                $npi = $dossierSession->npi;
                $user = User::where('npi', $npi)->firstOrFail();
                $candidat_id = $user->id;
                $categorie_permis_id = $dossierSession->categorie_permis_id;
                $dossier_candidat_id = $dossierSession->dossier_candidat_id;
                $dossier_session_id = $dossierSession->id;
                $date_soumission = now();
                $message = '';

                // Vérifier le champ resultat_code
                if ($dossierSession->resultat_code === 'success') {
                    $message = "Félicitations ! Vous avez réussi l'examen du code avec une note de : " . $finalNote;
                } else {
                    $message = "Désolé, vous avez échoué à l'examen du code. Note obtenue : " . $finalNote;
                }

                // Créer un enregistrement dans ParcoursSuivi
                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $npi;
                $parcoursSuivi->slug = "resultat-code";
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $candidat_id;
                $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                $parcoursSuivi->message = $message;
                $parcoursSuivi->dossier_session_id = $dossier_session_id;
                $parcoursSuivi->date_action = $date_soumission;
                $parcoursSuivi->save();
            }

            return $this->successResponse($parcoursSuivi, 'Convocation envoyée avec succès.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            logger()->error($e);
            return $this->errorResponse('Dossier candidat non trouvé.', null, null, 422);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de l\'insertion', null, null, 500);
        }
    }
}
