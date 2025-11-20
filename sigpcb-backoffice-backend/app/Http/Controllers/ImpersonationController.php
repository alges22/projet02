<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Base\Impersonation;
use App\Services\Api;


class ImpersonationController extends ApiController
{

    public function createImpersonation(Request $request)
    {
        $this->hasAnyPermission(["all"]);

        $validator = Validator::make($request->all(), [
            'user_type' => 'required|string',
            'admin_npi' => 'required',
            'user_npi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            $responseP = Api::base('GET', "candidats/" . $request->input('admin_npi'));

            // Vérifier la réponse de l'API externe
            if (!$responseP->successful()) {
                return $this->errorResponse("Le numéro NPI de l'administrateur n'existe pas chez l'ANIP.", 422);
            }

            $responseU = Api::base('GET', "candidats/" . $request->input('user_npi'));

            // Vérifier la réponse de l'API externe
            if (!$responseU->successful()) {
                return $this->errorResponse("Le numéro NPI de l'utilisateur n'existe pas chez l'ANIP.", 422);
            }
            // Vérifier s'il existe déjà une entrée correspondant au couple user_type, admin_npi, user_npi
            $existingImpersonation = Impersonation::where('user_type', $request->user_type)
                ->where('admin_npi', $request->admin_npi)
                ->where('user_npi', $request->user_npi)
                ->first();

            if ($existingImpersonation) {
                // Vérifier si le token précédent est toujours valide
                if ($existingImpersonation->expire_at > Carbon::now()) {
                    return $this->successResponse('Un token d\'impersonation est déjà en cours pour cette demande');
                }
            }

            // Récupérer l'URL appropriée en fonction du type d'utilisateur
            $loginUrl = $this->getLoginUrl($request->user_type);

            // Générer un token unique
            $token = Str::random(60);

            // Définir l'heure d'expiration
            $expireAt = Carbon::now()->addHours(1);

            // Créer un nouvel enregistrement d'impersonation
           $imper = Impersonation::create([
                'user_type' => $request->user_type,
                'admin_npi' => $request->admin_npi,
                'user_npi' => $request->user_npi,
                'expire_at' => $expireAt,
                'token' => $token,
                'login_url' => $loginUrl,
            ]);

            return $this->successResponse($imper,'Impersonation créée avec succès');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation', 500);
        }
    }


    // Fonction pour obtenir l'URL de connexion appropriée en fonction du type d'utilisateur
    private function getLoginUrl($userType)
    {
        if ($userType === 'candidat') {
            return env('CandidatLogin', ''); // Récupère l'URL de connexion du fichier .env pour le candidat
        } else if ($userType === 'promoteur') {
            return env('AutoEcoleLogin', ''); // Récupère l'URL de connexion du fichier .env pour l'auto-école
        }
    }
}
