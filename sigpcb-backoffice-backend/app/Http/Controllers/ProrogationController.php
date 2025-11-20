<?php

namespace App\Http\Controllers;

use App\Mail\EserviceMail;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\Candidat\Candidat;
use Illuminate\Support\Facades\DB;
use App\Models\Candidat\Prorogation;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\ProrogationRejet;
use Illuminate\Support\Facades\Validator;
use App\Models\Candidat\EserviceParcourSuivi;


class ProrogationController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "read-permit-extension","edit-permit-extension"]);

        $query = Prorogation::orderByDesc('id');

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
        $this->hasAnyPermission(["all","edit-permit-extension"]);

        $validator = Validator::make($request->all(), [
            'prorogation_id' => 'required|integer|min:1',
            'consigne' => 'required|max:5000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Démarrez une transaction de base de données
            DB::beginTransaction();

            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = Prorogation::findOrFail($request->prorogation_id);

            $state = 'validate';
            $da->update([
                'state' => $state,
                'date_validation' => now(),
            ]);

            $consigne = $request->consigne;
            $npi = $da->npi;

            // Utilisation de firstOrFail pour s'assurer que le candidat existe
            $daUser = Candidat::where('npi', $npi)->firstOrFail();
            $candidat_id = $daUser->id;

            $email = $da->email;

            // Notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-prorogation-validate',
                'service' => 'Prorogation',
                'candidat_id' => $candidat_id,
                'message' => 'Votre demande de prorogation a été validée avec succès. Consigne :' . $consigne,
                'eservice' => '{"Model":"Prorogation","id":"' . $da->id . '"}',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);

            // Envoyer un e-mail au candidat
            Mail::to($email)->send(new EserviceMail($consigne));

            // Confirmer la transaction si tout s'est bien passé
            DB::commit();

            // Retourner une réponse significative
            return $this->successResponse([
                'message' => 'La demande a été validée avec succès.',
                'validated_demande' => $da,
                'parcours_suivi' => $parcoursSuivi,
            ]);
        } catch (\Throwable $th) {
            logger()->error($th);
            // Annuler la transaction en cas d'erreur
            DB::rollBack();
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation', 500);
        }
    }


    public function rejectDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-permit-extension"]);

        $validator = Validator::make($request->all(), [
            'prorogation_id' => 'required|integer|min:1',
            'consigne' => 'max:5000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Utilisation de firstOrFail pour s'assurer que l'utilisateur existe
            $da = Prorogation::findOrFail($request->prorogation_id);

            $npi = $da->npi;
            // Utilisation de firstOrFail pour s'assurer que le candidat existe
            $daUser = Candidat::where('npi', $npi)->firstOrFail();
            $candidat_id = $daUser->id;

            // Utilisation d'une transaction
            DB::beginTransaction();

            try {
                $state = 'rejected';
                $da->update([
                    'state' => $state,
                    'date_rejet' => now(),
                ]);

                // Créer une nouvelle entrée dans la table
                $ProrogationRejet = new ProrogationRejet();
                $ProrogationRejet->prorogation_id = $da->id;
                $ProrogationRejet->motif = $request->consigne;
                $ProrogationRejet->state = 'init';
                $ProrogationRejet->save();

                //notifier le candidat
                $parcoursSuiviData = [
                    'npi' => $npi,
                    'slug' => 'demande-prorogation-rejected',
                    'service' => 'Prorogation',
                    'candidat_id' => $candidat_id,
                    'message' => 'Votre demande de prorogation a été rejetée. Consigne : ' . $request->consigne,
                    'eservice' => '{"Model":"ProrogationRejet","id":"' . $ProrogationRejet->id . '"}',
                    'date_action' => now(),
                ];

                // Créer le parcours suivi
                $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);

                // Commit de la transaction si tout se passe bien
                DB::commit();

                // Retourner une réponse significative
                return $this->successResponse([
                    'message' => 'La demande a été rejetée avec succès.',
                    'rejected_demande' => $da,
                    'parcours_suivi' => $parcoursSuivi,
                ]);
            } catch (\Throwable $th) {
                // En cas d'erreur, annuler la transaction
                DB::rollBack();
                throw $th; // Rethrow l'exception pour la gestion ultérieure
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet', 500);
        }
    }

}
