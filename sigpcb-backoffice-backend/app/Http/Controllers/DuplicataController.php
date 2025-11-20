<?php

namespace App\Http\Controllers;

use App\Mail\EserviceMail;
use App\Models\AnnexeAnatt;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\Candidat\Candidat;
use App\Models\Candidat\Duplicata;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Candidat\ParcoursSuivi;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\DuplicataRejet;
use Illuminate\Support\Facades\Validator;
use App\Models\Candidat\EserviceParcourSuivi;

class DuplicataController extends ApiController
{
    public function index(Request $request)
    {
        try {
            $this->hasAnyPermission(["all", "read-permit-replacement-duplicate","edit-permit-replacement-duplicate"]);

            $query = Duplicata::orderByDesc('id');
            // Appliquer les filtres
            $query = $this->applyFilters($query);

            $demandes = $query->paginate(10);

            // Obtient les annexe distincts des demandes
            $annexeIds = $demandes->filter(function ($demande) {
                return !is_null($demande->annexe_id) && $demande->annexe_id !== '';
            })->pluck('annexe_id')->unique();

            // Obtient les informations des annexes en fonction des valeurs de annexe_id
            $annexes = AnnexeAnatt::whereIn('id', $annexeIds)->get()->keyBy('id');

            // Obtient les npi distincts des demandes
            $npiCollection = $demandes->filter(function ($demande) {
                return !is_null($demande->npi) && $demande->npi !== '';
            })->pluck('npi')->unique();

            // Obtient les candidats en fonction des valeurs de npi
            $candidats = collect(GetCandidat::get($npiCollection->all()));

            // Associe les informations des annexes et des candidats aux demandes
            $demandes->each(function ($demande) use ($annexes, $candidats) {
                $annexe = $annexes->get($demande->annexe_id);
                $demande->annexe_info = $annexe;

                $demandeur = $candidats->where('npi', $demande->npi)->first();
                $demande->demandeur_info = $demandeur;

                return $demande;
            });

            // Retourner la réponse avec pagination
            return $this->successResponse($demandes);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', 500);
        }
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
        // Filtre par type
        if ($type = request('type')) {
            $query->where('type', $type);
        }

        return $query;
    }

    public function validateDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-permit-replacement-duplicate"]);


        $validator = Validator::make($request->all(), [
            'duplicata_id' => 'required|integer|min:1',
            'consigne' => 'required|max:5000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Démarrez une transaction de base de données
            DB::beginTransaction();

            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = Duplicata::findOrFail($request->duplicata_id);

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
                'slug' => 'demande-duplicata-validate',
                'service' => 'Duplicata',
                'candidat_id' => $candidat_id,
                'message' => 'Votre demande de duplicata a été validée avec succès. Consigne :' . $consigne,
                'eservice' => '{"Model":"Duplicata","id":"' . $da->id . '"}',
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
        $this->hasAnyPermission(["all","edit-permit-replacement-duplicate"]);

        $validator = Validator::make($request->all(), [
            'duplicata_id' => 'required|integer|min:1',
            'consigne' => 'max:5000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = Duplicata::findOrFail($request->duplicata_id);

            $npi = $da->npi;

            // Utilisation de firstOrFail pour s'assurer que le candidat existe
            $daUser = Candidat::where('npi', $npi)->firstOrFail();
            $candidat_id = $daUser->id;

            $state = 'rejected';
            $da->update([
                'state' => $state,
                'date_rejet' => now(),
            ]);
            // Créer une nouvelle entrée dans la table
            $duplicataRejet = new DuplicataRejet();
            $duplicataRejet->duplicata_id = $da->id;
            $duplicataRejet->motif = $request->consigne;
            $duplicataRejet->state = 'init';
            $duplicataRejet->save();

            // Notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-duplicata-rejected',
                'service' => 'Duplicata',
                'candidat_id' => $candidat_id,
                'message' => 'Votre demande de duplicata de permis a été rejetée. Consigne : ' . $request->consigne,
                'eservice' => '{"Model":"DuplicataRejet","id":"' . $duplicataRejet->id . '"}',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet', 500);
        }
    }

}
