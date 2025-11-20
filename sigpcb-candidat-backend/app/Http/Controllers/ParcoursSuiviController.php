<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Sms;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\CandidatJustifAbsence;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ParcoursSuiviController extends ApiController
{

    private function sendSMS($to)
    {
        $user = env('SMS_LOGIN');
        $password = env('SMS_PASSWORD');
        $apikey = env('SMS_APIKEY');
        $from = 'ANaTT+BENIN';

        $text = 'Votre+auto-ecole+vient+de+valider+votre+formation+veuillez+vous+connecter+pour+choisir+votre+session';

        $url = env('SMS_ENDPOINT') . "?user={$user}&password={$password}&apikey={$apikey}&from={$from}&to={$to}&text={$text}";

        $response = Http::get($url);

        return $response->successful();
    }

    public function index()
    {
        try {
            // Sous-requête pour obtenir les ID maximum pour chaque groupe (service, categorie_permis_id)
            $subquery = ParcoursSuivi::select('service', 'categorie_permis_id', DB::raw('MAX(id) as max_id'))
                ->groupBy('service', 'categorie_permis_id');
            // Requête principale pour récupérer toutes les données correspondant aux ID maximum
            $data = ParcoursSuivi::joinSub($subquery, 'max_ids', function ($join) {
                $join->on('parcours_suivis.id', '=', 'max_ids.max_id');
            })->orderBy('id', 'desc')->get();

            // Retourner une réponse de succès avec les données
            return $this->successResponse($data);
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', 500);
        }
    }

    public function getAll()
    {
        try {
            $suivis = ParcoursSuivi::all();
            // Retourner une réponse de succès avec les données
            return $this->successResponse($suivis);
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/parcours-suivis/{candidat_id}",
     *      operationId="getParcoursSuivisByCandidatId",
     *      tags={"ParcoursSuivis"},
     *      summary="Affiche les détails d'un parcours d'un candidat",
     *      description="Affiche les détails d'un parcours d'un candidat enregistré dans la base de données",
     *      @OA\Parameter(
     *          name="candidat_id",
     *          description="ID du candidat dont on veut récupéré le parcours",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Détails du parcours candidat récupéré",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="candidat_id",
     *                  description="ID de candidat",
     *                  type="integer",
     *                  example=1
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="parcours candidat non trouvé"
     *      )
     * )
     */
    public function show($candidat_id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            if($user->id != $candidat_id){
                return $this->errorResponse('Vous ne pouvez pas suivre un autre candidat.', null, null, 422);
            }
            // Sous-requête pour obtenir les ID maximum pour chaque groupe (service, categorie_permis_id)
            $subquery = ParcoursSuivi::select('service', 'categorie_permis_id', DB::raw('MAX(id) as max_id'))
                ->where('candidat_id', $candidat_id)
                ->groupBy('service', 'categorie_permis_id');

            // Requête principale pour récupérer toutes les données correspondant aux ID maximum
            $data = ParcoursSuivi::joinSub($subquery, 'max_ids', function ($join) {
                $join->on('parcours_suivis.id', '=', 'max_ids.max_id');
            })->get();

            // Retourner une réponse de succès avec les données
            return $this->successResponse($data);
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', 500);
        }
    }



    /**
     * @OA\Post(
     *      path="/api/anatt-candidat/parcours-suivis",
     *      operationId="createParcoursSuivis",
     *      tags={"ParcoursSuivis"},
     *      summary="Crée un nouvel parcours candidat",
     *      description="Crée un nouvel parcours candidat enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="candidat_id",
     *                      description="ID de du candidat",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="service",
     *                      description="Service de l'action",
     *                      type="string",
     *                      example="Inscription"
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto école",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="agent_id",
     *                      description="ID de l'agent ayant fait l'action ",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat ",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_session_id",
     *                      description="ID du dossier session du candidat ",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID du catégorie de permis ",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="npi du candidat ",
     *                      type="string",
     *                      example=1234567890
     *                  ),
     *                  @OA\Property(
     *                      property="slug",
     *                      description="slug de l'action ",
     *                      type="string",
     *                      example="preinscription"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      description="le message de l'action ",
     *                      type="string",
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvel parcours candidat créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouvel parcours candidat créé",
     *                  type="integer",
     *              ),
     *      )
     * )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Validation des données de la requête
            $validator = Validator::make($request->all(), [
                'service' => 'required|string',
                'telephone' => 'required',
                'candidat_id' => 'required|exists:candidats,id',
                'auto_ecole_id' => 'nullable',
                'agent_id' => 'nullable',
                'categorie_permis_id' => 'required',
                'npi' => 'nullable|string',
                'slug' => 'nullable|string',
                'message' => 'nullable|string',
                'bouton' => 'nullable|string',
                'action' => 'nullable|string',
                'url' => 'nullable|string',
                'dossier_candidat_id' => 'nullable|exists:dossier_candidats,id',
                'dossier_session_id' => 'nullable|exists:dossier_sessions,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            $datesoumission = now();
            // Créer l'objet et enregistrer dans la base de données
            $model = new ParcoursSuivi();
            $model->service = $request->input('service');
            $model->candidat_id = $request->input('candidat_id');
            $model->auto_ecole_id = $request->input('auto_ecole_id');
            $model->agent_id = $request->input('agent_id');
            $model->categorie_permis_id = $request->input('categorie_permis_id');
            $model->npi = $request->input('npi');
            $model->slug = $request->input('slug');
            $model->message = $request->input('message');
            $model->bouton = $request->input('bouton');
            $model->action = $request->input('action');
            $model->url = $request->input('url');
            $model->date_action = $datesoumission;
            $model->dossier_candidat_id = $request->input('dossier_candidat_id');
            $model->dossier_session_id = $request->input('dossier_session_id');
            $model->save();
            $tel = $request->input('telephone');

            $country_code = '229';
            $num = $request->input('telephone');
            $text = 'Votre auto-école vient de valider votre formation, veuillez vous connecter pour choisir votre session';
            Sms::sendSMS($country_code, $num, $text);

            // Retourner une réponse de succès avec les données créées
            return $this->successResponse($model, 'Données enregistrées avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse('Une erreur s\'est produite lors de l\'enregistrement des informations.', 500);
        }
    }

    public function storeConvocationCode(Request $request)
    {
        $v = Validator::make($request->all(), [
            'examen_id' => 'required|integer',
            'annexe_id' => 'required|integer',
            'agent_id' => 'required|integer',
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors());
        }

        try {
            $examenId = $request->input('examen_id');
            $annexeId = $request->input('annexe_id');
            $agentId = $request->input('agent_id');
            $candidatEndpoint = env('CANDIDAT_FRONT_ENDPOINT');

            // Obtenez toutes les sessions de dossiers correspondantes
            $dossierSessions = DossierSession::where('examen_id', $examenId)
                ->where('annexe_id', $annexeId)
                ->where('closed', false)
                ->where('state', 'validate')
                ->get();

            if ($dossierSessions->isEmpty()) {
                return $this->errorResponse("Aucun candidat trouvé");
            }
            $parcoursSuivi = null;
            foreach ($dossierSessions as $dossierSession) {
                $npi = $dossierSession->npi;
                $dossierId = $dossierSession->id;
                $categorie_permis_id = $dossierSession->categorie_permis_id;
                $dossier_candidat_id = $dossierSession->dossier_candidat_id;
                $date_soumission = now();
                $message = "Vous avez été programmé. Veuillez cliquer sur le bouton pour télécharger votre convocation.";
                $url = route('generate-convocation', ['encryptedDossierId' => Crypt::encrypt($dossierId)]);
                $user = User::where('npi', $npi)->firstOrFail();
                $candidat_id = $user->id;
                // Vérifier si une entrée ParcoursSuivi existe déjà avec les mêmes critères
                $existingEntry = ParcoursSuivi::where('dossier_session_id', $dossierId)
                    ->where('categorie_permis_id', $categorie_permis_id)
                    ->where('slug', 'convocation-code')
                    ->first();

                if ($existingEntry) {
                    // Mettre à jour l'entrée existante avec la nouvelle URL
                    $existingEntry->url = $url;
                    $existingEntry->save();
                } else {
                    // Créer une nouvelle entrée dans ParcoursSuivi
                    $parcoursSuivi = new ParcoursSuivi();
                    $parcoursSuivi->npi = $npi;
                    $parcoursSuivi->slug = "convocation-code";
                    $parcoursSuivi->service = 'Permis';
                    $parcoursSuivi->candidat_id = $candidat_id;
                    $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                    $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                    $parcoursSuivi->message = $message;
                    $parcoursSuivi->agent_id = $agentId;
                    $parcoursSuivi->bouton = json_encode(['bouton' => 'Convocation', 'status' => '1']);
                    $parcoursSuivi->dossier_session_id = $dossierId;
                    $parcoursSuivi->url = $url;
                    $parcoursSuivi->date_action = $date_soumission;
                    $parcoursSuivi->save();
                }
            }

            return $this->successResponse($parcoursSuivi, 'Traitement effectué avec succès.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            logger()->error($e);
            return $this->errorResponse('Dossier candidat non trouvé.', null, null, 422);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de l\'insertion', null, null, 500);
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


    public function storeConvocationConduite(Request $request)
    {
        $v = Validator::make($request->all(), [
            'examen_id' => 'required|integer',
            'annexe_id' => 'required|integer',
            'agent_id' => 'required|integer',
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors());
        }

        try {
            $examenId = $request->input('examen_id');
            $annexeId = $request->input('annexe_id');
            $agentId = $request->input('agent_id');

            // Obtenez toutes les sessions de dossiers correspondantes
            $dossierSessions = DossierSession::where('examen_id', $examenId)
                ->where('annexe_id', $annexeId)
                ->where('resultat_code', 'success')
                ->get();

            if ($dossierSessions->isEmpty()) {
                return $this->errorResponse("Aucun candidat trouvé");
            }
            $parcoursSuivi = null;
            foreach ($dossierSessions as $dossierSession) {
                $npi = $dossierSession->npi;
                $dossierId = $dossierSession->id;
                $categorie_permis_id = $dossierSession->categorie_permis_id;
                $dossier_candidat_id = $dossierSession->dossier_candidat_id;
                $date_soumission = now();
                $message = "Vous avez été programmé pour la conduite. Veuillez cliquer sur le bouton pour télécharger votre convocation.";
                $url = route('generate-conduite-convocation', ['encryptedDossierId' => Crypt::encrypt($dossierId)]);
                $user = User::where('npi', $npi)->firstOrFail();
                $candidat_id = $user->id;
                // Vérifier si une entrée ParcoursSuivi existe déjà avec les mêmes critères
                $existingEntry = ParcoursSuivi::where('dossier_session_id', $dossierId)
                    ->where('categorie_permis_id', $categorie_permis_id)
                    ->where('slug', 'convocation-conduite')
                    ->first();

                if ($existingEntry) {
                    // Mettre à jour l'entrée existante avec la nouvelle URL
                    $existingEntry->url = $url;
                    $existingEntry->save();
                } else {
                    // Créer une nouvelle entrée dans ParcoursSuivi
                    $parcoursSuivi = new ParcoursSuivi();
                    $parcoursSuivi->npi = $npi;
                    $parcoursSuivi->slug = "convocation-conduite";
                    $parcoursSuivi->service = 'Permis';
                    $parcoursSuivi->candidat_id = $candidat_id;
                    $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                    $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                    $parcoursSuivi->message = $message;
                    $parcoursSuivi->agent_id = $agentId;
                    $parcoursSuivi->bouton = json_encode(['bouton' => 'Convocation', 'status' => '1']);
                    $parcoursSuivi->dossier_session_id = $dossierId;
                    $parcoursSuivi->url = $url;
                    $parcoursSuivi->date_action = $date_soumission;
                    $parcoursSuivi->save();
                }
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
