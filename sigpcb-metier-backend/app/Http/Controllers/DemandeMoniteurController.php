<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Api;
use App\Services\Help;
use App\Models\Moniteur;
use Illuminate\Http\Request;
use App\Models\DemandeMoniteur;
use App\Models\DemandeExaminateur;
use Illuminate\Support\Facades\DB;
use App\Models\DemandeMoniteurRejet;
use App\Models\EserviceParcourSuivi;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Models\DemandeExaminateurRejet;
use Illuminate\Support\Facades\Validator;

class DemandeMoniteurController extends ApiController
{
    public function store(Request $request)
    {
        try {
            // Utilisation de transactions pour garantir l'intégrité des données
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'categorie_permis_ids' => 'required',
                'permis_file' => 'required|image',
                'diplome_file' => 'required|image',
            ]);
            if ($validator->fails()) {
                DB::rollBack();
                return $this->errorResponse('Une erreur est survenue', $validator->errors()->toArray());
            }
            $npi =  $request->input('npi');
            $responseP = Api::base('GET', "candidats/" . $request->input('npi'));

            // Vérifier la réponse de l'API externe
            if (!$responseP->successful()) {
                return $this->errorResponse("Le numéro NPI n'existe pas chez l'ANIP.", 422);
            }
            //vérification si l'utilisateur existe déjà en tant que npi ou moniteur
            $userNPI = Moniteur::where('npi',$npi)->first();
            if (!empty($userNPI)) {
                DB::rollBack();
                return $this->errorResponse("Le numéro npi indiqué est déja utilisé pour un compte Moniteur, connecter vous plutot");
            }

            $permisFile=null;
            if ($request->hasFile('permis_file')) {
                $permisFile = $request->file('permis_file')->store('permis_file', 'public');
            }

            $diplomeFile=null;
            if ($request->hasFile('diplome_file')) {
                $diplomeFile = $request->file('diplome_file')->store('diplome_file', 'public');
            }

            $user = Moniteur::create([
                'npi' => $npi,
                'email' => $request->input('email'),
            ]);

            $demande = DemandeMoniteur::create([
                'email' => $request->input('email'),
                'npi' => $npi,
                'num_permis' => $request->input('num_permis'),
                'categorie_permis_ids' => json_encode($request->input('categorie_permis_ids')),
                'permis_file' => $permisFile,
                'diplome_file' => $diplomeFile,
                'moniteur_id' => $user->id,
                'state' => 'init',
            ]);

            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-moniteur',
                'service' => 'Moniteur',
                'candidat_id' => $user->id,
                'message' => "Votre demande a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                'date_action' => now(),
            ];
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);
            DB::commit();
                return $this->successResponse($demande,'Demande créée avec succès');
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error($e);
            return $this->errorResponse('Erreur lors de la création de le demande', $e->getMessage(), '', 500);
        }
    }

    public function getDemande($id)
    {
        try {
            $rejet = DemandeMoniteurRejet::findOrFail($id);

            if (!$rejet) {
                return $this->errorResponse('Le rejet de la demande est introuvable');
            }

            $demandeID = $rejet->demande_moniteur_id;
            $demande = DemandeMoniteur::find($demandeID);

            if (!$demande) {
                return $this->errorResponse('La demande moniteur est introuvable');
            }

            return $this->successResponse($demande);
        } catch (\Throwable $e) {
            logger()->error($e);
            $message = 'Une erreur s\'est produite : ' . $e->getMessage();
            return $this->errorResponse("Une erreur est survenue lors de la récupération de la demande moniteur.");
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // Vérifier l'existence du rejet
            $Rejet = DemandeMoniteurRejet::find($id);

            if (!$Rejet) {
                return $this->errorResponse('Le rejet est introuvable', null, 404);
            }
            // Vérifier l'existence de la demande associée
            $demandeID = $Rejet->demande_moniteur_id;
            $demande = DemandeMoniteur::find($demandeID);

            if (!$demande) {
                return $this->errorResponse('La demande associée est introuvable', null, 404);
            }

            // Validation des données de la requête
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'categorie_permis_ids' => 'required',
                'email' => 'required|email',
                'permis_file' => 'image',
                'diplome_file' => 'image',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors()->toArray());
            }
            $user = Help::moniteurAuth();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $permisFile = null;

            try {
                if ($request->hasFile('permis_file')) {
                    $permisFile = $request->file('permis_file')->store('permis_file', 'public');
                }
            } catch (\Throwable $fileException) {
                return $this->errorResponse('Erreur lors du téléchargement du fichier de permis', null, 500);
            }

            $diplomeFile = null;

            try {
                if ($request->hasFile('diplome_file')) {
                    $diplomeFile = $request->file('diplome_file')->store('diplome_file', 'public');
                }
            } catch (\Throwable $fileException) {
                return $this->errorResponse('Erreur lors du téléchargement du fichier diplome', null, 500);
            }

            // Commencer la transaction
            DB::beginTransaction();

            try {
                // Mettre à jour la demande
                $demande->update([
                    'email' => $request->email,
                    'num_permis' => $request->num_permis,
                    'categorie_permis_ids' =>  json_encode($request->input('categorie_permis_ids')),
                    'email' => $request->email,
                    'state' => 'pending',
                ]);

                // Mettre à jour le rejet d'échange
                $state = 'pending';
                $Rejet->update([
                    'state' => $state,
                    'date_correction' => now(),
                ]);

                if ($permisFile !== null) {
                    $demande->update(['permis_file' => $permisFile]);
                }
                if ($diplomeFile !== null) {
                    $demande->update(['diplome_file' => $diplomeFile]);
                }
                $npi = $demande->npi;
                $parcoursSuiviData = [
                    'npi' => $npi,
                    'slug' => 'correction-demande',
                    'service' => 'Moniteur',
                    'candidat_id' => $user->id,
                    'message' => "Votre correction a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                    'date_action' => now(),
                ];
                $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);
                // Valider la transaction
                DB::commit();

                return $this->successResponse($demande, 'Mise à jour effectuée avec succès');
            } catch (\Throwable $e) {
                // Annuler la transaction en cas d'erreur
                DB::rollBack();
                logger()->error($e);
                return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, 500);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, 500);
        }
    }

    public function getEserviceByCandidatId()
    {
        try {
            // Obtenir l'utilisateur connecté
            $user = Help::moniteurAuth();

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
