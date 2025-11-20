<?php

namespace App\Http\Controllers;

use App\Models\Inspecteur;
use App\Services\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SuiviCandidatController extends ApiController
{

    /**
     * @OA\Get(
     *     path="/api/anatt-admin/suivi-candidats",
     *     summary="Obtenir la liste des dossiers de suivi des candidats",
     *     description="Récupère la liste des dossiers de suivi des candidats enregistrés",
     *     operationId="getAllSuiviCandidat",
     *     tags={"SuiviCandidat"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des dossiers de suivi des candidats",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du suivi",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="npi du candidat",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID de la catégorie de permis",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="langue_id",
     *                      description="ID de la langue de composition",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier candidat",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="chapitres_id",
     *                      description="ID des chapitres que le candidat a suivi",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut ",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="certification",
     *                      description="La case certification a cocher par l'auto école",
     *                      type="string"
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(['all', 'read-dossier-validation','edit-dossier-validation']);

        // Vérifier si l'utilisateur est authentifié
        $user = Auth::user();
        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }
        $request->merge([
            'scope' => "admin",
        ]);
        // Vérifier si l'utilisateur est un inspecteur
        $inspecteur = Inspecteur::where('user_id', $user->id)->first();
        if ($inspecteur) {
            // Si l'utilisateur est un inspecteur, récupérer annexe_anatt_id
            $request->merge([
                'annexe_anatt_id' => $inspecteur->annexe_anatt_id,
            ]);
        }
        return $this->exportFromBase('suivi-candidats', $request->all());
    }

    public function validateSuivi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suivi_id' => "required|integer|min:1"
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), statuscode: 422);
        }
        try {

            $stateResponse = Api::base('POST', "dossier-sessions/state", [
                'dossier_session_id' => $request->suivi_id,
                'state' => "validate",
                "agent_id" => auth()->id()
            ]);

            $ds = Api::data($stateResponse);

            $data['dossier_session_id'] = $ds['id'];
            $data['agent_id'] =  auth()->id();
            $data['dossier_candidat_id'] =  $ds['dossier_candidat_id'];
            $data['state'] =  'validate';


            // Mettre à jour le champ "state" du dossier candidat via l'endpoint update-dossier-state sur l'instance candidat
            $response = Api::base('POST', "updat-dossier-state", $data);
            $data = Api::data($response);

            return $this->successResponse("Suivi candidat validé avec succès", statuscode: 201);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation', statusCode: 500);
        }
    }

    public function rejectSuivi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suivi_id' => "required|integer|min:1",
            'consigne' => "max:5000",
            "motif" => "required"
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), statuscode: 422);
        }

        try {
            $suivi_data =
                [
                    'dossier_session_id' => $request->suivi_id,
                    'state' => "rejet",
                    "agent_id" => auth()->id(),
                    "motif" => $request->motif,
                    "consignes" => nl2br($request->consigne)
                ];
            $stateResponse = Api::base('POST', "dossier-sessions/state", $suivi_data);

            if (!$stateResponse->successful()) {
                return $this->errorResponse($stateResponse->json('message', "Une erreur est survenue"), $stateResponse->json('errors', []), statuscode: $stateResponse->status());
            }
            $ds = Api::data($stateResponse);

            $data['dossier_session_id'] = $ds['id'];
            $data['dossier_candidat_id'] = $ds['dossier_candidat_id'];
            $data['consignes'] = $request->consigne;
            $data['motif'] = $request->motif;
            $data['agent_id'] =  auth()->id();
            $data['state'] =  'rejet';

            // Mettre à jour le champ "state" du dossier candidat via l'endpoint update-dossier-state sur l'instance candidat
            $response = Api::base('POST', "updat-dossier-state", $data);

            if (!$response->successful()) {
                return $this->errorResponse($response->json('message', "Une erreur est survenue"), $response->json('errors', []), statuscode: $response->status());
            }
            Api::data($response);

            return $this->successResponse($ds, "Suivi candidat rejeté avec succès", statuscode: 201);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet', statusCode: 500);
        }
    }

    public function rejets(Request $request)
    {
        $request->merge([
            'scope' => "admin"
        ]);
        return $this->exportFromBase('suivi-candidats/rejets', $request->all());
    }
}
