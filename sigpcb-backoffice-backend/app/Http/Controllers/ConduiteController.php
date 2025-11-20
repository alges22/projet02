<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Services\Api;
use Illuminate\Http\Request;

class ConduiteController extends ApiController
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
        $response = Api::base('POST', 'conduite/generate', $request->all());

        $responseData = $response->json();


        # Important pour que le chef puisse savoir qu'il devra créer de un examen
        if (!$response->successful()) {
            return $this->errorResponse($response->json('message'), statuscode: $response->status());
        }
        $data = Api::data($response);

        return $this->successResponse($data, $responseData['message']);
    }

    public function resultatCode()
    {

        $response = Api::base('GET', 'conduite/resultat-code',  request()->all());

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

    public function programmations()
    {
        $this->hasAnyPermission(["all", "edit-programming"]);
        $response = Api::base('GET', 'conduite/programmations',  request()->all());

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

    public function juryDitribution(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-programming"]);

        $response = Api::base('POST', 'conduite/jury-distribution', $request->all());

        $responseData = $response->json();

        if (!$response->successful()) {
            return $this->errorResponse($response->json('message'), statuscode: $response->status());
        }
        $data = Api::data($response);

        return $this->successResponse($data, $responseData['message']);
    }
}
