<?php

namespace App\Http\Controllers;

use App\Services\Help;
use App\Mail\LicenceMail;
use App\Mail\AutoEcoleMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Mail\ConfirmationMail;
use App\Models\AutoEcole\Licence;
use App\Models\AutoEcole\Agrement;
use App\Models\AutoEcole\Moniteur;
use App\Models\AutoEcole\Vehicule;
use Illuminate\Support\Facades\DB;
use App\Models\AutoEcole\AutoEcole;
use Illuminate\Support\Facades\Mail;
use App\Models\AutoEcole\DemandeLicence;
use App\Models\AutoEcole\DemandeAgrement;
use Illuminate\Support\Facades\Validator;
use App\Models\AutoEcole\DemandeLicenceRejet;
use App\Models\AutoEcole\Promoteur;
use App\Models\AutoEcole\DemandeAgrementRejet;

class DemandeLicenceController extends ApiController
{

    public function generateUniqueCode()
    {
        $code = '';

        do {
            // Générer un code aléatoire
            $code = strtoupper(Str::random(16));

            // Vérifier s'il est unique
        } while (Licence::where('code', $code)->exists());

        return $code;
    }

    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "read-licences-management","edit-licences-management"]);

        $query = DemandeLicence::with(['autoecole', 'fiche', 'autoecole.commune', 'autoecole.departement']);
        // Appliquer les filtres
        $query->orderByDesc('id');
        $query = $this->applyFilters($query);

        $demandes = $query->paginate(10);
        // on initialise une collection vide pour stocker les npi
        $npiCollection = collect();

        // on itère sur chaque demande d'agrément
        foreach ($demandes as $demande) {
            // Ajoute le promoteur_npi à la collection si non nul
            if (!is_null($demande->npi)) {
                $npiCollection->push($demande->npi);
            }

            // Ajoute les moniteurs à la collection si non nuls
            $moniteurs = $demande->moniteurs;
            if (!empty($moniteurs)) {
                $npiCollection = $npiCollection->merge($moniteurs);
            }
        }

        // Retire les valeurs nulles ou vides de la collection
        $npiCollection = $npiCollection->filter(function ($npi) {
            return !is_null($npi) && $npi !== '';
        });

        // Retire les doublons de la collection
        $npiCollection = $npiCollection->unique();

        // Obtient les candidats en fonction des valeurs de npi
        $candidats = collect(GetCandidat::get($npiCollection->all()));


        // Associe les informations des candidats aux demandes d'agrément
        foreach ($demandes as $demande) {
            // Associe le promoteur
            $promoteur = $candidats->where('npi', $demande->npi)->first();
            $demande->promoteur_info = $promoteur;

            // Associe les moniteurs
            $moniteursInfo = [];
            foreach ($demande->moniteurs as $moniteurNpi) {
                $moniteur = $candidats->where('npi', $moniteurNpi)->first();
                if ($moniteur) {
                    $moniteursInfo[] = $moniteur;
                }
            }
            $demande->moniteurs_info = $moniteursInfo;
        }

        return $this->successResponse($demandes);
    }

    public function applyFilters($query)
    {
        // Filtre par département
        if (request()->has('departement_id')) {
            $query = $query->where('departement_id', request('departement_id'));
        }

        // Filtre par commune
        if (request()->has('commune_id')) {
            $query = $query->where('commune_id', request('commune_id'));
        }

        // Filtre par état (state)
        if (request()->has('state')) {
            $states = explode(',', request()->get('state'));

            $query = $query->whereIn('state', $states);
        }

        // Filtre par recherche
        if (request()->has('search')) {
            $searchTerm = request('search');
            $query->where(function ($query) use ($searchTerm) {
                $query->whereHas('autoecole', function ($autoecoleQuery) use ($searchTerm) {
                    $autoecoleQuery->where('name', 'LIKE', "%$searchTerm%")
                        ->orWhere('num_ifu', 'LIKE', "%$searchTerm%");
                });
            });
        }

        return $query;
    }

    public function validateDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-licences-management"]);

        $validator = Validator::make($request->all(), [
            'd_licence_id' => "required|integer|min:1",
            'autoecole_name' => "required",
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), statuscode: 422);
        }

        try {
            DB::beginTransaction();
            $dl = DemandeLicence::find($request->d_licence_id);
            if (!$dl) {
                return $this->errorResponse("La demande est introuvable");
            }

            $state = 'validate';
            $dl->update([
                'state' => $state,
                'date_validation' => now(),

            ]);
            $autoecoleId = $dl->auto_ecole_id;
            $autoEcole = AutoEcole::find($autoecoleId);
            if (!$autoEcole) {
                return $this->errorResponse("L'auto école demandée est introuvable");
            }

            $autoEcole->update([
                'status' => true,
            ]);

            // Créer une nouvelle entrée dans la table "Agrements"
            $licence = new Licence();
            $licence->auto_ecole_id = $dl->auto_ecole_id;
            $licence->status = true;
            $licence->date_debut =  now()->toDateString();
            $licence->date_fin = now()->addYear()->toDateString();
            $licence->code = $this->generateUniqueCode();
            $licence->save();

            $autoecole_id = $dl->auto_ecole_id;
            $moniteurs = $dl->moniteurs;

            // Sélectionnez tous les moniteurs liés à l'auto-école
            Moniteur::where('auto_ecole_id', $autoecole_id)
                ->where('active', true)
                ->update(['active' => false]);

            // Enregistrer les nouveaux NPI
            foreach ($moniteurs as $npi) {
                Moniteur::create([
                    'npi' => $npi,
                    'auto_ecole_id' => $autoecole_id,
                    'active' => true,
                ]);
            }

            $vehicules = $dl->vehicules;
            // Enregistrer les vehicule
            foreach ($vehicules as $vehicule) {
                Vehicule::create([
                    'immatriculation' => $vehicule['immatriculation'],
                    'auto_ecole_id' => $autoecole_id,
                ]);
            }

            $date_fin = $licence->date_fin;
            $name = $request->input('autoecole_name');

            $npi_promoteur = $dl->npi;
            $promoteurInfo = Promoteur::where('npi',$npi_promoteur)->first();
            if (!$promoteurInfo) {
                return $this->errorResponse("Le promoteur est introuvable");
            }

            $email = $promoteurInfo->email;
            Mail::to($email)->send(new LicenceMail($name, $date_fin));
            // Notifier le promoteur
            $promoteur = $dl->promoteur;
            $autoecole = $dl->autoecole;
            Help::historique(
                'licence',
                'Demande de licence validée',
                'demande-licence-validate',
                'Votre demande de licence a été validée avec succès',
                $promoteur,
                $dl,
            );
            DB::commit();
            return $this->successResponse($dl);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation', statusCode: 500);
        }
    }

    public function rejectDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-licences-management"]);

        $validator = Validator::make($request->all(), [
            'd_licence_id' => "required|integer|min:1",
            'consigne' => "max:5000",
            "motif" => "required"
        ]);
        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), statuscode: 422);
        }

        try {
            $da = DemandeLicence::find($request->d_licence_id);
            if (!$da) {
                return $this->errorResponse("La demande est introuvable");
            }
            $promoteur = $da->promoteur;
            $autoecole = $da->autoecole;
            $state = 'rejected';
            $da->update([
                'state' => $state,
                'date_rejet' => now(),

            ]);

            // Créer une nouvelle entrée dans la table "DemandeRejet"
            $DemandeLicenceRejet = new DemandeLicenceRejet();
            $DemandeLicenceRejet->demande_licence_id = $da->id;
            $DemandeLicenceRejet->motif = $request->motif;
            $DemandeLicenceRejet->state = 'init';
            $DemandeLicenceRejet->save();

            // Notifier le promoteur
            Help::historique(
                'licence',
                'Demande de licence rejetée',
                'demande-licence-rejected',
                'Votre demande de licence a été rejetée pour le motif suivant : ' . $request->motif . '. Consigne : ' . $request->consigne,
                $promoteur,
                $DemandeLicenceRejet,
            );

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet', statusCode: 500);
        }
    }
}
