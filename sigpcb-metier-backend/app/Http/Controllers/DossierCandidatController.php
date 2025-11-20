<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Services\Api;
use App\Models\Candidat;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierSession;
use App\Models\CandidatPayment;
use App\Models\DossierCandidat;
use App\Services\Mail\Messager;
use App\Models\PermisNumPayment;
use Illuminate\Http\UploadedFile;
use PhpParser\Node\Stmt\TryCatch;
use App\Models\EserviceParcourSuivi;
use App\Services\Mail\EmailNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Services\DossierCandidat\FullDossierDetails;
use App\Services\DossierCandidat\CreateCandidatDossier;
use App\Models\Authenticite;
use App\Models\AuthenticiteRejet;
use App\Models\Duplicata;
use App\Models\DuplicataRejet;
use App\Models\Echange;
use App\Models\EchangeRejet;
use App\Models\PermisInternational;
use App\Models\PermisInternationalRejet;

class DossierCandidatController extends ApiController
{
    public function getEserviceByCandidatId()
    {
        try {
            // Obtenir l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $id = $user->id;

            // Obtenir les informations groupées par service depuis la table EserviceParcourSuivi
            $eserviceData = EserviceParcourSuivi::where('candidat_id', $id)
                ->orderByDesc('created_at')
                ->get()->map(function ($item) {
                    $eserviceInfo = json_decode($item->eservice, true);
                    // Récupérer les informations du modèle en fonction du champ eservice
                    $modelName = $eserviceInfo['Model'] ?? null;
                    $modelId = $eserviceInfo['id'] ?? null;

                    if ($modelName && $modelId) {
                        $modelData = app("App\\Models\\$modelName")->find($modelId);

                        if ($modelData) {
                            $item->model_info = $modelData;
                        }
                    }

                    return $item->makeHidden('eservice');
                })
                ->groupBy('service');
                if ($eserviceData->isEmpty()) {
                    return $this->successResponse([], 'Aucune information trouvée pour cet utilisateur', 200);
                }

                return $this->successResponse($eserviceData->values());
            } catch (\Throwable $e) {
                logger()->error($e);
                return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', null, null, 500);
            }
        }

        
}
