<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Api;
use App\Mail\EserviceMail;
use App\Models\AnnexeAnatt;
use App\Models\Examinateur;
use App\Mail\NewUserWelcome;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\Moniteur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Models\Examinateur\MoniteurNewI;
use Illuminate\Support\Facades\Validator;
use App\Models\ExaminateurCategoriePermis;
use App\Models\Examinateur\DemandeMoniteur;
use App\Models\Examinateur\ExaminateurNewI;
use App\Models\Examinateur\DemandeExaminateur;
use App\Models\Examinateur\DemandeMoniteurRejet;
use App\Models\Examinateur\DemandeExaminateurRejet;
use App\Models\Examinateur\ExaminateurParcourSuivi;



class DemandeMoniteurController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "read-monitor-recruitment","edit-monitor-recruitment"]);

        $query = DemandeMoniteur::orderByDesc('id');

        // Appliquer les filtres
        $query = $this->applyFilters($query);

        $demandes = $query->paginate(10);

        // Obtient les npi distincts des demandes d'agrément
        $npiCollection = $demandes->filter(function ($demande) {
            return !is_null($demande->npi) && $demande->npi !== '';
        })->pluck('npi')->unique();

        // Obtient les candidats en fonction des valeurs de npi
        $candidats = collect(GetCandidat::get($npiCollection->all()));

        // Associe les informations des candidats aux demandes d'agrément
        $demandes->each(function ($demande) use ($candidats) {
            $demandeur = $candidats->where('npi', $demande->npi)->first();
            $demande->demandeur_info = $demandeur;

        });

        return $this->successResponse($demandes);
    }


    public function applyFilters($query)
    {
        // Filtre par état (state)
        if ($state = request('state')) {
            $states = explode(',', $state);
            $query->whereIn('state', $states);
        }
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('npi', 'LIKE', "%$search%");
        }

        return $query;
    }


    public function validateDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-monitor-recruitment"]);

        $validator = Validator::make($request->all(), [
            'npi' => 'required',
            'demande_moniteur_id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse('La validation a échoué', $validator->errors()->toArray(), 422);
        }

        $responseP = Api::base('GET', "candidats/" . $request->input('npi'));

        // Vérifier la réponse de l'API externe
        if (!$responseP->successful()) {
            return $this->errorResponse("Le numéro NPI n'existe pas chez l'ANIP.", 422);
        }

        $userAuth = Auth::user();

        if (!$userAuth) {
            return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
        }

        $agent_id = $userAuth->id;
        try {
            // Démarrez une transaction de base de données
            DB::beginTransaction();

            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = DemandeMoniteur::findOrFail($request->demande_moniteur_id);
            $email = $da->email;
            // Vérifier l'unicité de l'email
            if (Moniteur::where('npi', $da->npi)->exists()) {
                DB::rollBack();
                return $this->errorResponse('Ce moniteur existe déjà dans la base de donnée des moniteurs',null, null, 422);
            }
            $state = 'validate';
            $da->update([
                'state' => $state,
                'date_validation' => now(),
            ]);

            $npi = $da->npi;

            // Utilisation de firstOrFail pour s'assurer que le candidat existe
            $daUser = MoniteurNewI::where('npi', $npi)->firstOrFail();
            $candidat_id = $daUser->id;

            // Générer un mot de passe aléatoire

            $data = [
                'npi' => $npi,
                'agent_id' => $agent_id,
                'date_validation' => now(),
            ];

            $user = Moniteur::create($data);

            // Notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-moniteur-validate',
                'service' => 'Moniteur',
                'candidat_id' => $candidat_id,
                'message' => 'Votre demande a été validée avec succès.',
                'eservice' => '{"Model":"DemandeMoniteur","id":"' . $da->id . '"}',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = ExaminateurParcourSuivi::create($parcoursSuiviData);


            // Confirmer la transaction si tout s'est bien passé
            DB::commit();

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponse('Une erreur s\'est produite lors de la validation', 500);
        }
    }


    public function rejectDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-monitor-recruitment"]);

        $validator = Validator::make($request->all(), [
            'demande_moniteur_id' => 'required|integer|min:1',
            'consigne' => 'max:5000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = DemandeMoniteur::findOrFail($request->demande_moniteur_id);

            $npi = $da->npi;

            // Utilisation de firstOrFail pour s'assurer que le candidat existe
            $daUser = MoniteurNewI::where('npi', $npi)->firstOrFail();
            $candidat_id = $daUser->id;

            $state = 'rejected';
            $da->update([
                'state' => $state,
                'date_rejet' => now(),
            ]);
            // Créer une nouvelle entrée dans la table
            $DemandeMoniteurRejet = new DemandeMoniteurRejet();
            $DemandeMoniteurRejet->demande_moniteur_id = $da->id;
            $DemandeMoniteurRejet->motif = $request->consigne;
            $DemandeMoniteurRejet->state = 'init';
            $DemandeMoniteurRejet->save();

            // Notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-moniteur-rejected',
                'service' => 'Moniteur',
                'candidat_id' => $candidat_id,
                'message' => 'Votre demande a été rejetée. Consigne : ' . $request->consigne,
                'eservice' => '{"Model":"DemandeMoniteurRejet","id":"' . $DemandeMoniteurRejet->id . '"}',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = ExaminateurParcourSuivi::create($parcoursSuiviData);

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet', 500);
        }
    }

}
