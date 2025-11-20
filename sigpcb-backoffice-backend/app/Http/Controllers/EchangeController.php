<?php

namespace App\Http\Controllers;

use App\Mail\EserviceMail;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\Candidat\Echange;
use App\Models\Candidat\Candidat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Candidat\EchangeRejet;
use App\Models\Candidat\ParcoursSuivi;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Models\Candidat\EserviceParcourSuivi;

class EchangeController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "read-permit-exchange","edit-permit-exchange"]);

        $query = Echange::orderByDesc('id');

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
        $this->hasAnyPermission(["all","edit-permit-exchange"]);

        $validator = Validator::make($request->all(), [
            'echange_id' => 'required|integer|min:1',
            'consigne' => 'required|max:5000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Démarrez une transaction de base de données
            DB::beginTransaction();

            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = Echange::findOrFail($request->echange_id);

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
                'slug' => 'demande-echange-validate',
                'service' => 'Echange',
                'candidat_id' => $candidat_id,
                'eservice' => '{"Model":"Echange","id":"' . $da->id . '"}',
                'message' => 'Votre demande d\'échange a été validée avec succès. Consigne :' . $consigne,
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);

            // Envoyer un email
            Mail::to($email)->send(new EserviceMail($consigne));

            // Confirmez la transaction si tout s'est bien passé
            DB::commit();

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation', 500);
        }
    }


    public function rejectDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-permit-exchange"]);

        $validator = Validator::make($request->all(), [
            'echange_id' => "required|integer|min:1",
            'consigne' => "max:5000",
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), statuscode: 422);
        }

        try {
            $da = Echange::findOrFail($request->echange_id);

            if (!$da) {
                return $this->errorResponse("La demande est introuvable");
            }

            $npi =$da->npi;
            $daUser = Candidat::where('npi', $npi)->firstOrFail();
            if (!$daUser) {
                return $this->errorResponse("ce candidat est introuvable");
            }
            $candidat_id =$daUser->id;

            $state = 'rejected';
            $da->update([
                'state' => $state,
                'date_rejet' =>now(),

            ]);

            // Créer une nouvelle entrée dans la table
            $EchangeRejet = new EchangeRejet();
            $EchangeRejet->echange_id = $da->id;
            $EchangeRejet->motif = $request->consigne;
            $EchangeRejet->state = 'init';
            $EchangeRejet->save();

            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-echange-rejected',
                'service' => 'Echange',
                'candidat_id' => $candidat_id,
                'message' =>'Votre demande d\'échange de permis a été rejetée. Consigne : ' . $request->consigne,
                'eservice' => '{"Model":"EchangeRejet","id":"' . $EchangeRejet->id . '"}',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet', statusCode: 500);
        }
    }
}
