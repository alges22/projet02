<?php

namespace App\Http\Controllers;

use App\Models\Base\CandidatExamenSalle;
use App\Models\Candidat\ConvocationCode;
use App\Models\Inspecteur;
use App\Services\Api;
use App\Services\GetCandidat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CodeInspectionController extends ApiController
{

    private function getInspectorId()
    {
        $userId = auth()->id();

        // Recherche de l'inspecteur associé à l'utilisateur connecté
        $inspector = Inspecteur::where('user_id', $userId)->first();

        if ($inspector) {
            // Si un inspecteur est trouvé, renvoyer son ID
            $inspecteur_id = $inspector->id;
            return $inspecteur_id;
        } else {
            // Si aucun inspecteur n'est trouvé, renvoyer un message d'erreur

            return $this->errorResponse("Vous devez être un inspecteur pour continuer sur cette page.");
        }
    }

    public function recapts(Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $params = [
                'inspecteur_id' => $inspectorId,
            ];
            $request->merge($params);
            return $this->exportFromBase("code-inspections/recapts", $request->all());
        } else {
            return $inspectorId;
        }
    }


    public function agendas(Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $params = [
                'inspecteur_id' => $inspectorId,
            ];

            $request->merge($params);
            return $this->exportFromBase("code-inspections/agendas", $request->all());
        } else {
            return $inspectorId; // Renvoyer le message d'erreur
        }
    }

    public function vagues(Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $params = [
                'inspecteur_id' => $inspectorId,
            ];
            $request->merge($params);
            return $this->exportFromBase("code-inspections/vagues", $request->all());
        } else {
            return $inspectorId; // Renvoyer le message d'erreur
        }
    }

    public function candidats($vague_id, Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $params = [
                'inspecteur_id' => $inspectorId,
            ];
            $request->merge($params);
            return $this->exportFromBase("code-inspections/vagues/" . $vague_id . "/candidats", $request->all());
        } else {
            return $inspectorId; // Renvoyer le message d'erreur
        }
    }

    public function markAsAbscent(Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $params = $request->merge([
                'inspecteur_id' => $inspectorId,
            ])->all();

            return $this->postToBase("code-inspections/mark-as-abscent", $params);
        } else {
            return $inspectorId;
        }
    }

    public function openSession(Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $params = $request->merge([
                'inspecteur_id' => $inspectorId,
            ])->all();

            return $this->postToBase("code-inspections/open-session", $params);
        } else {
            return $inspectorId;
        }
    }

    public function stopCandidatCompo(Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $params = $request->merge([
                'inspecteur_id' => $inspectorId,
            ])->all();

            return $this->postToBase("code-inspections/stop-candidat-compo", $params);
        } else {
            return $inspectorId;
        }
    }

    public function emarges(Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $params = $request->merge([
                'inspecteur_id' => $inspectorId,
            ])->all();

            return $this->postToBase("code-inspections/emarges", $params);
        } else {
            return $inspectorId;
        }
    }

    public function pause(Request $request)
    {
        $inspectorId = $this->getInspectorId();
        if (is_numeric($inspectorId)) {
            $params = $request->merge([
                'inspecteur_id' => $inspectorId,
            ])->all();

            return $this->postToBase("code-inspections/pause", $params);
        } else {
            return $inspectorId;
        }
    }

    public function startCompo(Request $request)
    {
        $response = Api::compo("POST", 'generate-questions', $request->all());

        # Retrait des informations d'entete
        $message = $response->json("message", "Une erreur est survenue est");
        $data = $response->json('data', null);
        $errors = $response->json('errors', null);
        $statuscode = $response->status();

        # S'il y a une erreur on retourne l'erreur telle quell
        if (!$response->successful()) {
            return $this->errorResponse($message, $errors, $data, $statuscode);
        }

        # On recupère la bonne information
        $data = Api::data($response);

        return $this->successResponse($data, $message, $statuscode);
    }

    public function resetCompo(Request $request)
    {

        $inspectorId = $this->getInspectorId();
        if (is_numeric($inspectorId)) {
            $params = $request->merge([
                'inspecteur_id' => $inspectorId,
            ])->all();

            return $this->postToBase("code-inspections/reset-compo", $params);
        } else {
            return $inspectorId;
        }
    }

    public function stopCompo(Request $request)
    {
        $response = Api::compo("POST", 'stop-compo', $request->all());
        # Retrait des informations d'entete
        $message = $response->json("message", "Une erreur est survenue ");
        $data = $response->json('data', null);
        $errors = $response->json('errors', null);
        $statuscode = $response->status();

        # S'il y a une erreur on retourne l'erreur telle quell
        if (!$response->successful()) {
            return $this->errorResponse($message, $errors, $data, $statuscode);
        }

        # On recupère la bonne information
        $data = Api::data($response);

        return $this->successResponse($data, $message, $statuscode);
    }

    public function salles(Request $request)
    {
        $inspectorId = $this->getInspectorId();

        if (is_numeric($inspectorId)) {
            $request->merge([
                'inspecteur_id' => $inspectorId,
            ]);

            return $this->exportFromBase("code-inspections/salles", $request->all());
        } else {
            return $inspectorId;
        }
    }

    public function verifyCandidat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $convocation = ConvocationCode::where('code', $request->code)->first();

        if (!$convocation) {
            return $this->errorResponse("Candidat non identifié", 404);
        }

        $candidat = CandidatExamenSalle::where('dossier_session_id', $convocation->dossier_session_id)->latest()->first();

        $data = GetCandidat::findOne($convocation->dossierSession->npi);


        $data['examen_id'] = $candidat->examen_id;
        $data['candidat_salle_id'] = $candidat->id;
        $data['salle_id'] = $candidat->salle_compo_id;


        return $this->successResponse($data, "Code de convocation valide", 200);
    }
}
