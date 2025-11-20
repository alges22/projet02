<?php

namespace App\Http\Controllers;

use App\Mail\EserviceMail;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\Candidat\Candidat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Models\Candidat\PermisInternational;
use App\Models\Candidat\EserviceParcourSuivi;
use App\Models\Candidat\PermisInternationalRejet;

class PermisInternationalController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "read-international-permit","edit-international-permit"]);

        $query = PermisInternational::orderByDesc('id');

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
        $this->hasAnyPermission(["all","edit-international-permit"]);

        $validator = Validator::make($request->all(), [
            'permis_international_id' => "required|integer|min:1",
            'consigne'=>"required|max:5000"
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), statuscode: 422);
        }

        try {
            // Démarrez une transaction de base de données
            DB::beginTransaction();
            $da = PermisInternational::findOrFail($request->permis_international_id);
            if (!$da) {
                return $this->errorResponse("La demande est introuvable");
            }

            $state = 'validate';
            $da->update([
                'state' => $state,
                'date_validation' =>now(),

            ]);
            $consigne = $request->consigne;
            $npi =$da->npi;
            $daUser = Candidat::where('npi', $npi)->firstOrFail();
            if (!$daUser) {
                return $this->errorResponse("ce candidat est introuvable");
            }
            $candidat_id =$daUser->id;
            $email = $da->email;
            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-permis-international-validate',
                'service' => 'Permis International',
                'candidat_id' => $candidat_id,
                'message' =>'Votre demande de permis international a été validée avec succès. Consigne :' .$consigne ,
                'eservice' => '{"Model":"PermisInternational","id":"' . $da->id . '"}',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);
            Mail::to($email)->send(new EserviceMail($consigne));
            // Confirmez la transaction si tout s'est bien passé
            DB::commit();
            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation', statusCode: 500);
        }
    }

    public function rejectDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-international-permit"]);

        $validator = Validator::make($request->all(), [
            'permis_international_id' => "required|integer|min:1",
            'consigne' => "max:5000",
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), statuscode: 422);
        }

        try {
            $da = PermisInternational::findOrFail($request->permis_international_id);

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
            $PermisInternationalRejet = new PermisInternationalRejet();
            $PermisInternationalRejet->permis_international_id = $da->id;
            $PermisInternationalRejet->motif = $request->consigne;
            $PermisInternationalRejet->state = 'init';
            $PermisInternationalRejet->save();

            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-permis-international-rejected',
                'service' => 'Permis International',
                'candidat_id' => $candidat_id,
                'message' =>'Votre demande de permis international a été rejetée. Consigne : ' . $request->consigne,
                'eservice' => '{"Model":"PermisInternationalRejet","id":"' . $PermisInternationalRejet->id . '"}',
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
