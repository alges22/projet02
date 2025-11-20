<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgrammationController extends ApiController
{
    public function generate(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-programming"]);

        $v = Validator::make($request->all(), [
            'annexe_id' => 'required|exists:annexe_anatts,id'
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors(), statuscode: 422);
        }
        $response = Api::base('POST', 'programmations/generate', $request->all());

        $responseData = $response->json();


        # Important pour que le chef puisse savoir qu'il devra créer de un examen
        if (!$response->successful()) {
            return $this->errorResponse($response->json('message'), statuscode: $response->status());
        }
        $data = Api::data($response);

        return $this->successResponse($data, $responseData['message']);
    }

    public function distributeIntoSalle(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-programming"]);;

        $response = Api::base('POST', 'programmations/distribute-into-salle', $request->all());

        $responseData = $response->json();

        if (!$response->successful()) {
            return $this->errorResponse($response->json('message') ?? "Une erreur inattendue est survenue", statuscode: $response->status());
        }
        $data = Api::data($response);

        return $this->successResponse($data, $responseData['message']);
    }

    public function statistiques()
    {
        $this->hasAnyPermission(["all", "edit-programming","read-programming"]);

        $response = Api::base('GET', 'programmations/statistiques',  request()->all());

        $responseData = $response->json() ?? [];
        $message = $responseData['message'] ?? null;
        if (!$response->successful()) {
            if (!$message) {
                $message = "Une erreur est survenue";
            }
            return $this->errorResponse($message, statuscode: $response->status());
        }
        $data = Api::data($response);

        return $this->successResponse($data, $message);
    }

    /**
     * Attention cette méthode est appélée dans
     * ProgrammationCode pour générer un PDF
     *
     */
    public function programmations()
    {
        $this->hasAnyPermission(["all", "edit-programming","read-programming"]);

        $response = Api::base('GET', 'programmations',  request()->all());

        $responseData = $response->json() ?? [];
        $message = $responseData['message'] ?? null;
        if (!$response->successful()) {
            if (!$message) {
                $message = "Une erreur est survenue lors de la programmation";
            }
            return $this->errorResponse($message, statuscode: $response->status());
        }
        $data = Api::data($response);

        return $this->successResponse($data, $message);
    }

}
