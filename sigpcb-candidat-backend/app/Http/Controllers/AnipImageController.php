<?php

namespace App\Http\Controllers;
use App\Models\Admin\AnnexeAnatt;
use App\Services\GetCandidat;
use Illuminate\Http\Request;

class AnipImageController extends ApiController
{
    public function index(Request $request)
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = auth()->user();

            // Vérifier si l'utilisateur est connecté
            if (!$user) {
                return $this->errorResponse("Utilisateur non connecté", null, null, 401);
            }

            // Récupérer le NPI de l'utilisateur
            $npi = $user->npi;

            // Vérifier si le NPI existe
            if (!$npi) {
                return $this->errorResponse("NPI non trouvé pour l'utilisateur", null, null, 404);
            }

            // Récupérer l'image de la personne via la méthode findOneImage
            $image = GetCandidat::findOneImage($npi);

            // Vérifier si une image a été trouvée
            if (!$image) {
                return $this->errorResponse("Image non trouvée pour l'utilisateur", 422);
            }

            // Retourner la réponse avec l'image ou toute autre donnée nécessaire
            return $this->successResponse($image);
        } catch (\Throwable $th) {
            // Log l'erreur et renvoyer une réponse d'erreur générique
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }


}
