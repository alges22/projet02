<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GetCandidat;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class NpiAnipController extends ApiController
{
    public function getNPI(Request $request)
    {
        try {
            // Récupérer le numéro NPI de la requête
            $validator = Validator::make(
                $request->all(),
                [
                    'npi' => 'required|numeric',
                ],
                [
                    "npi.required" => "Le champ  NPI ne peut pas être vide",
                    "npi.numeric"  => "Le champ NPI doit contenir uniquement des chiffres"
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }

            $npi = $request->input('npi');

            // Effectuer la requête GET à l'autre API avec le numéro NPI
            $data = GetCandidat::findOne($npi);

            // Vérifier la réponse de l'API externe
            if ($data) {
                return $this->successResponse($data);
            } else {
                // Retourner une réponse d'erreur
                return $this->errorResponse('Le numéro npi n\'existe pas.', 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse("Le NPI {$request->npi} n'a pas été trouvé.", 500);
        }
    }
}