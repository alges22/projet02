<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Services\Help;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\Admin\AnattMoniteur;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class NpiANaTTController extends ApiController
{
    public function getNPI(Request $request)
    {
        try {
            // Récupérer le numéro NPI de la requête
            $validator = Validator::make(
                $request->all(),
                [
                    'npi' => 'required',
                ],
                [
                    "npi.required" => "Le champ  NPI ne peut pas être vide",
                    "npi.numeric"  => "Le champ NPI doit contenir uniquement des chiffres"
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse("Validation échouée", $validator->errors());
            }

            $npi = $request->input('npi');

            // Effectuer la requête GET à l'autre API avec le numéro NPI
            try {
                $data = GetCandidat::findOne($npi);
            } catch (\Throwable $th) {
                return $this->errorResponse("Numéro NPI non identifié. Veuillez corriger");
            }

            // Vérifier la réponse de l'API externe
            if ($data) {
                $data['wasPromoteur']  = User::whereNpi($npi)->exists();
                $data['wasMoniteur']  = AnattMoniteur::whereNpi($npi)->exists();
                return $this->successResponse([
                    'npi' => $data['npi'],
                    'avatar' => $data['avatar'],
                    'nom' => $data['nom'],
                    'prenoms' => $data['prenoms'],
                    'wasPromoteur' => $data['wasPromoteur'],
                    'date_de_naissance' => $data['date_de_naissance'],
                    'lieu_de_naissance' => $data['lieu_de_naissance'],
                    'adresse' => $data['adresse'],
                    'sexe' => $data['sexe'],
                    'wasMoniteur' => $data['wasMoniteur'],
                    "telephone" => Help::hashPhone($data['telephone'] ?? '')
                ]);
            } else {
                // Retourner une réponse d'erreur
                return $this->errorResponse('Le numéro npi n\'existe pas.', 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des données NPI.', 500);
        }
    }
}