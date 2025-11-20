<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CandidatConduiteReponseController extends ApiController
{


    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'sub_bareme_id' => 'array',
                    'bareme_conduite_id' => 'required',
                    'jury_candidat_id' => 'required|integer',
                ],
            );

            if ($validator->fails()) {
                return $this->errorResponseclient("La validation a échoué.", $validator->errors(), null, 422);
            }

            // Effectuer la requête POST à l'API en utilisant votre méthode d'assistance
            $response = Api::base('POST', 'candidat-conduite-reponses', $request->all());

            // Vérifier la réponse de l'API externe
            if ($response->ok()) {
                $responseData = $response->json();
                return $this->successResponseclient($responseData, 'Candidat conduite réponse créé avec succès.', 200);
            } else {
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la création.';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function show($jury_candidat_id)
    {
        return $this->exportFromBase("candidat-conduite-reponses/" . $jury_candidat_id);
    }
}
