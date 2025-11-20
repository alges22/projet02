<?php

namespace App\Http\Controllers;

use auth;
use Carbon\Carbon;
use App\Services\Help;
use App\Models\Candidat;
use App\Models\Base\Langue;
use App\Models\Recrutement;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\RecrutementRejet;
use App\Models\Admin\AnnexeAnatt;
use App\Models\Base\CategoriePermis;
use App\Models\EntrepriseParcourSuivi;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RecrutementController extends ApiController
{
    public function index()
    {
        try {
            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 401 );

            }
            $user_id = $entreprise->id;
    
            // Sélectionner les recrutements pour l'entreprise de l'utilisateur connecté
            $query = Recrutement::where('entreprise_id', $user_id);
            $query->orderByDesc('id');
            $query = $this->applyFilters($query);
            $recrutements = $query->paginate(10);
    
            foreach ($recrutements as $recrutement) {
                // Récupérer les IDs de la catégorie de permis et de l'annexe
                $categorie_permis_id = $recrutement->categorie_permis_id;
                $annexe_id = $recrutement->annexe_id;
    
                // Rechercher la correspondance dans les tables CategoriePermis et AnnexeAnatt
                $categorie_permis = CategoriePermis::find($categorie_permis_id);
                $annexe = AnnexeAnatt::find($annexe_id);
                $recrutement->categorie_permis=$categorie_permis;
                $recrutement->annexe=$annexe;
            }
    
            if ($recrutements->isEmpty()) {
                // Aucun recrutement trouvé
                return $this->successResponse([], 'Aucun recrutement trouvé pour cette entreprise');
            }
    
            return $this->successResponse($recrutements, 'Liste des recrutements récupérée avec succès');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Erreur serveur', $th->getMessage(), null, 500);
        }
    }    

    public function applyFilters($query)
    {
        // Filtre par état (state)
        if (request()->has('state')) {
            $states = explode(',', request()->get('state'));
    
            $query = $query->whereIn('state', $states);
        }
        return $query;
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|integer', 
                'annexe_id' => 'required|integer', 
                'date_compo' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        $today = Carbon::now()->format('Y-m-d');
                        if ($value < $today) {
                            $fail('La date de composition ne peut pas être antérieure à la date d\'aujourd\'hui.');
                        }
                    },
                ],
            ]);
            
            // Vérifier les erreurs de validation
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }
            $existingRecrut = Recrutement::where('categorie_permis_id', $request->categorie_permis_id)
            ->where('annexe_id', $request->annexe_id)
            ->where('date_compo', $request->date_compo)
            ->exists();
            if ($existingRecrut) {
                return $this->errorResponse('Cette session existe déjà', null, null, 422);
            }
            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 500);

            }
            $user_id = $entreprise->id;
            // Créer une nouvelle entrée dans la base de données
            $recrutement = Recrutement::create([
                'categorie_permis_id' => $request->categorie_permis_id,
                'date_compo' => $request->date_compo,
                'entreprise_id' => $user_id,
                'annexe_id' => $request->annexe_id,
                'state' => 'init',
            ]);

            $parcoursSuiviData = [
                'slug' => 'demande-recrutement',
                'service' => 'Recrutement',
                'entreprise_id' => $user_id,
                'recrutement_id' => $recrutement->id,
                'message' => "Votre demande de recrutement a été créée avec succès.",
                'date_action' => now(),
            ];
    
            // Créer le parcours suivi
            $parcoursSuivi = EntrepriseParcourSuivi::create($parcoursSuiviData);
            return $this->successResponse($recrutement, 'Enregistrement réussi');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Erreur serveur', $th->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {

            $recrutement = Recrutement::find($id);

            if (!$recrutement) {
                return $this->errorResponse('Aucun résultat trouvé', 404);
            }

            $candidats = Candidat::where('recrutement_id',$id)->get();
            if (!$candidats) {
                return $this->errorResponse('Aucun candidat trouvé pour cette session', 404);
            }
            
            return $this->successResponse($recrutement);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la session.', 500);
        }
    }
    public function getMotif($id)
    {
        try {

            $recrutement = Recrutement::find($id);

            if (!$recrutement) {
                return $this->errorResponse('Aucun résultat trouvé', 404);
            }

            $rejet = RecrutementRejet::where('recrutement_id',$id)
            ->where('state', 'init')
            ->get();
            if (!$rejet) {
                return $this->errorResponse('Aucun rejet trouvé pour cette session', 404);
            }
            $recrutement->rejet = $rejet;
            return $this->successResponse($recrutement);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération du rejet.', 500);
        }
    }

    
    public function showCandidat($id)
    {
        try {

            $candidat = Candidat::find($id);

            if (!$candidat) {
                return $this->errorResponse('Aucun résultat trouvé', 404);
            }
            
            return $this->successResponse($candidat);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la session.', 500);
        }
    }

    public function candidatByRecrutement($id)
    {
        try {
            $recrutement = Recrutement::find($id);
    
            if (!$recrutement) {
                return $this->errorResponse('Aucun résultat trouvé', [], 404);
            }
    
            $query = Candidat::where('recrutement_id', $id)->orderByDesc('id');
            $query = $this->applyCandidatFilters($query);
            $candidats = $query->paginate(10);
    
            // Obtient les npi distincts
            $npiCollection = $candidats->filter(function ($candidat) {
                return !is_null($candidat->npi) && $candidat->npi !== '';
            })->pluck('npi')->unique();
    
            // Obtient les candidats en fonction des valeurs de npi
            $candidatsInfo = collect(GetCandidat::get($npiCollection->all()));
    
            $candidats->each(function ($candidat) use ($candidatsInfo) {
                $info = $candidatsInfo->where('npi', $candidat->npi)->first();
                $candidat->candidat_info = $info;
            });
            $candidats->each(function ($candidat) {
                $langueId = $candidat->langue_id;
                $langue = Langue::find($langueId);
                $candidat->langue = $langue;

                $recrutementId=$candidat->recrutement_id;
                $recrutement = Recrutement::find($recrutementId);
                $candidat->session = $recrutement;

                $categorie_permis_id=$recrutement->categorie_permis_id;
                $annexe_id=$recrutement->annexe_id;

                $categorie_permis = CategoriePermis::find($categorie_permis_id);
                $annexe = AnnexeAnatt::find($annexe_id);
                $candidat->categorie_permis=$categorie_permis;
                $candidat->annexe=$annexe;
            });
            
            return $this->successResponse($candidats);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la session.', $e->getMessage(), 500);
        }
    }
    public function applyCandidatFilters($query)
    {
        // Filtre par état (state)
        if (request()->has('state')) {
            $states = explode(',', request()->get('state'));
    
            $query = $query->whereIn('state', $states);
        }
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('npi', 'LIKE', "%$search%");
        }
            
        return $query;
    }

    
    public function update(Request $request, $id)
    {
        try {
            // Valider la requête
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|integer',
                'annexe_id' => 'required|integer',
                'date_compo' => 'required|date',
            ]);
    
            // Vérifier les erreurs de validation
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }
    
            // Vérifier si le recrutement existe
            $recrutement = Recrutement::find($id);
            if (!$recrutement) {
                return $this->errorResponse('Session non trouvée', null, null, 404);
            }
            $state = $recrutement->state;
            if ($state === 'validate') {
                return $this->errorResponse('Cette session a déjà été validée par l\'ANaTT et ne peut donc plus être modifié', null, null, 404);
            }

            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 500);

            }
            $user_id = $entreprise->id;
    
            if ($recrutement->entreprise_id !== $user_id) {
                return $this->errorResponse("Vous n'avez pas la permission de mettre à jour cette session", null, null, 403);
            }
            // Vérifier s'il existe une autre entrée avec les mêmes valeurs
            $existingRecrut = Recrutement::where('categorie_permis_id', $request->categorie_permis_id)
            ->where('annexe_id', $request->annexe_id)
            ->where('date_compo', $request->date_compo)
            ->where('id', '<>', $id) // Exclure la ligne actuelle
            ->exists();

            if ($existingRecrut) {
                return $this->errorResponse('Une session avec les mêmes valeurs existe déjà', null, null, 422);
            }
    
            // Mettre à jour le recrutement
            $recrutement->categorie_permis_id = $request->categorie_permis_id;
            $recrutement->annexe_id = $request->annexe_id;
            $recrutement->date_compo = $request->date_compo;
            $recrutement->save();
    
            return $this->successResponse($recrutement, 'Mise à jour réussie');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Erreur serveur', $th->getMessage(), null, 500);
        }
    }

    public function sendSession(Request $request, $id)
    {
        try {
            // Vérifier si le recrutement existe
            $recrutement = Recrutement::find($id);
    
            if (!$recrutement) {
                return $this->errorResponse('Session non trouvée', null, null, 404);
            }
    
            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 500);

            }
            $user_id = $entreprise->id;
    
            if ($recrutement->entreprise_id !== $user_id) {
                return $this->errorResponse("Vous n'avez pas la permission de mettre à jour cette session", null, null, 403);
            }
            // Mettre à jour le recrutement
            $recrutement->finished = true;
            $recrutement->state = 'pending';
            $recrutement->save();

            $parcoursSuiviData = [
                'slug' => 'send-recrutement',
                'service' => 'Recrutement',
                'entreprise_id' => $user_id,
                'recrutement_id' => $recrutement->id,
                'message' => "Votre demande de recrutement a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                'date_action' => now(),
            ];
    
            // Créer le parcours suivi
            $parcoursSuivi = EntrepriseParcourSuivi::create($parcoursSuiviData);
            return $this->successResponse($recrutement, 'Mise à jour réussie');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Erreur serveur', $th->getMessage(), null, 500);
        }
    }

    public function updateRecrutementRejet(Request $request, $id)
    {
        try {
            // Vérifier l'existence du rejet
            $Rejet = RecrutementRejet::find($id);
    
            if (!$Rejet) {
                return $this->errorResponse('Le rejet est introuvable', null, 404);
            }
            // Vérifier l'existence de la demande associée
            $demandeID = $Rejet->recrutement_id;
            $demande = Recrutement::find($demandeID);
    
            if (!$demande) {
                return $this->errorResponse('La demande associée est introuvable', null, 404);
            }
    
            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 500);

            }
            $user_id = $entreprise->id;
    
            if ($demande->entreprise_id !== $user_id) {
                return $this->errorResponse("Vous n'avez pas la permission de mettre à jour cette session", null, null, 403);
            }
    
            try {
                // Mettre à jour la demande
                $demande->update([
                    'state' => 'pending',
                ]);
    
                // Mettre à jour le rejet 
                $state = 'pending';
                $Rejet->update([
                    'state' => $state,
                    'date_correction' => now(),
                ]);
    

                $parcoursSuiviData = [
                    'slug' => 'correction-recrutement',
                    'service' => 'Recrutement',
                    'entreprise_id' => $user_id,
                    'recrutement_id' => $demande->id,
                    'message' => "Votre correction a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                    'date_action' => now(),

                ];
                $parcoursSuivi = EntrepriseParcourSuivi::create($parcoursSuiviData);
                // Valider la transaction
    
                return $this->successResponse($demande, 'Mise à jour effectuée avec succès');
            } catch (\Throwable $e) {
                logger()->error($e);
                return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, 500);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, 500);
        }
    }


    public function getParcourForEntreprise()
    {
        try {
            // Obtenir l'utilisateur connecté
            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 500);
            }
            
            // Obtenir les recrutements de l'entreprise
            $recrutements = Recrutement::where('entreprise_id', $entreprise->id)
                                        ->orderByDesc('updated_at')
                                        ->get();
            
            // Tableau pour stocker les données de tous les recrutements
            $recrutementsData = [];
            
            // Parcourir chaque recrutement
            foreach ($recrutements as $recrutement) {
                // Initialiser les données du recrutement
                $recrutementData = $recrutement->toArray();
                
                // Obtenir les informations de parcours pour ce recrutement
                $eserviceData = EntrepriseParcourSuivi::where('entreprise_id', $entreprise->id)
                    ->where('recrutement_id', $recrutement->id)
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function ($item) {
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
                    });
                    $categorie_permis_id = $recrutement->categorie_permis_id;
                    $annexe_id = $recrutement->annexe_id;
                    $categorie_permis = CategoriePermis::find($categorie_permis_id);
                    $annexe = AnnexeAnatt::find($annexe_id);
                    // Associer les données de parcours au recrutement
                    $recrutementData['categorie_permis'] = $categorie_permis;
                    $recrutementData['annexe'] = $annexe;
                    $recrutementData['parcours'] = $eserviceData;
                    $recrutementsData[] = $recrutementData;
            }
            
            return $this->successResponse($recrutementsData);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', null, null, 500);
        }
    }

    
    public function storeCandidat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'recrutement_id' => 'required|exists:recrutements,id',
                'num_permis' => 'required',
                'permis_file' => 'required|image',
                'langue_id' => 'required',
            ]);
    
            // Vérifier les erreurs de validation
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }
    
            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 500);

            }
            $user_id = $entreprise->id;

            $existingCandidat = Candidat::where('npi', $request->npi)
            ->where('recrutement_id', $request->recrutement_id)
            ->where('entreprise_id', $request->user_id)
            ->exists();

            if ($existingCandidat) {
                return $this->errorResponse('Ce candidat a déjà été enregistré pour cette session', null, null, 422);
            }

            // Vérifier si le recrutement existe
            $recrutement = Recrutement::find($request->recrutement_id);
            if (!$recrutement) {
                return $this->errorResponse('Session non trouvée', null, null, 404);
            }
            $finished=$recrutement->finished;
            if ($finished!==false) {
                return $this->errorResponse('Vous avez déjà fermé cette session, vous ne pouvez plus enregistrer de nouveau candidats.', null, null, 404);
            }

            $permisFile=null;
            if ($request->hasFile('permis_file')) {
                $permisFile = $request->file('permis_file')->store('permis_file', 'public');
            }
            $npiData = [
                'npi' => $request->input('npi'),
                'recrutement_id' => $request->input('recrutement_id'),
                'entreprise_id' => $user_id,
                'num_permis' => $request->input('num_permis'),
                'permis_file' => $permisFile,
                'langue_id' => $request->input('langue_id'),
                'state' =>'init',

            ];

            $candidat = Candidat::create($npiData);

            return $this->successResponse($candidat, 'Mise à jour réussie');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Erreur serveur', $th->getMessage(), null, 500);        
        }
    }

    public function updateCandidat(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'recrutement_id' => 'required|exists:recrutements,id',
                'num_permis' => 'required',
                'langue_id' => 'required',
                'permis_file' => 'nullable|image',
            ]);

            // Vérifier les erreurs de validation
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 500);
            }

            // Vérifier si le candidat existe
            $candidat = Candidat::find($id);
            if (!$candidat) {
                return $this->errorResponse('Ce candidat n\'a pas été trouvé.', null, null, 422);
            }
            $recrutement = Recrutement::find($request->recrutement_id);
            $state = $recrutement->state;
            if ($state === 'validate') {
                return $this->errorResponse('Cette session a déjà été validée par l\'ANaTT et ne peut donc plus être modifié', null, null, 404);
            }
            // Vérifier s'il y a déjà un candidat enregistré avec les mêmes informations pour cette session
            $existingCandidat = Candidat::where('npi', $request->npi)
                ->where('recrutement_id', $request->recrutement_id)
                ->where('entreprise_id', $entreprise->id)
                ->where('id', '!=', $id) // Exclure le candidat actuel de la recherche
                ->exists();

            if ($existingCandidat) {
                return $this->errorResponse('Un candidat avec ces informations existe déjà pour cette session', null, null, 422);
            }

            // Mettre à jour les données du candidat
            $candidat->update([
                'npi' => $request->input('npi'),
                'recrutement_id' => $request->input('recrutement_id'),
                'num_permis' => $request->input('num_permis'),
                'langue_id' => $request->input('langue_id'),
            ]);

            // Mettre à jour l'image du candidat si elle est fournie dans la requête
            if ($request->hasFile('permis_file')) {
                $permisFile = $request->file('permis_file')->store('permis_file', 'public');
                $candidat->update(['permis_file' => $permisFile]);
            }

            return $this->successResponse($candidat, 'Mise à jour réussie');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Erreur serveur', $th->getMessage(), null, 500);
        }
    }


    public function destroy($id)
    {
        try {
            $recrutement = Recrutement::find($id);
    
            // Vérifier si l'entreprise existe
            if (!$recrutement) {
                return $this->errorResponse('Ce recrutement n\'a pas été trouvé.', null, 422);
            }
    
            // Vérifier s'il y a des entreprises liées à cette entreprise
            $candidats = Candidat::where('recrutement_id', $id)->get();
    
            if (!$candidats->isEmpty()) {
                return $this->errorResponse('Cette session ne peut pas être supprimée actuellement car elle est associée à des candidats.', null, 422);
            }
    
            $recrutement->delete();
    
            return $this->successResponse($recrutement, 'La session a été supprimée avec succès.');
        } catch (\Throwable $th) {
            // Gérer les erreurs, enregistrer l'erreur dans les journaux et renvoyer une réponse d'erreur générique
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
    

    public function deleteCandidat($id)
    {
        try {
            $entreprise = Help::entrepriseAuth();
            if (!$entreprise) {
                return $this->errorResponse("Vous devez être connecté en tant qu'entreprise pour continuer", null, null, 500);
            }

            // Vérifier si le candidat existe
            $candidat = Candidat::find($id);
            if (!$candidat) {
                return $this->errorResponse('Ce candidat n\'a pas été trouvé.', null, null, 422);
            }

            // Vérifier si l'entreprise est autorisée à supprimer ce candidat
            if ($candidat->entreprise_id !== $entreprise->id) {
                return $this->errorResponse('Vous n\'avez pas l\'autorisation de supprimer ce candidat.', null, null, 403);
            }
            $recrutementId=$candidat->recrutement_id;
            $recrutement = Recrutement::find($recrutementId);
            $state = $recrutement->state;
            if ($state === 'validate') {
                return $this->errorResponse('Cette session a déjà été validée par l\'ANaTT et ne peut donc plus être modifié', null, null, 404);
            }
            // Supprimer l'image associée au candidat s'il en a une
            if ($candidat->permis_file) {
                Storage::disk('public')->delete($candidat->permis_file);
            }

            // Supprimer le candidat
            $candidat->delete();

            return $this->successResponse($candidat, 'Le candidat a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

}
