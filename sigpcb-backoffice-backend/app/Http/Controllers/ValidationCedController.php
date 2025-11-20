<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidationCedController extends ApiController
{
    public function index()
    {
        try {
            // Effectuer la requête GET à l'API des validation-ced
            $validationRespon = Api::base("GET", "validation-ced", request()->all());

            // Obtenir les données des suivi candidats
            $data = Api::data($validationRespon);

            return $this->successResponse($data);
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponseclient($validationRespon->json("message"), statusCode: $validationRespon->status());
        }
    }

    public function validateJustif(Request $request)
    {
        //Ceci ajoute l'agent qui a validdé
        $request->request->set('agent_id', auth()->id());
        try {
            $response = Api::base('POST', 'validation-ced/validation', $request->all());


            $responseData = $response->json();

            if (!$response->successful()) {
                return $this->errorResponse($response->json('message'), $response->json("errors", []), statuscode: $response->status());
            }
            $data = Api::data($response);

            return $this->successResponse($data, $responseData['message']);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation', statusCode: 500);
        }
    }
}
