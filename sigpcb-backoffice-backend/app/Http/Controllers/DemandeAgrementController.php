<?php

namespace App\Http\Controllers;

use App\Services\Dgi;
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
use App\Models\AutoEcole\Promoteur;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Models\AutoEcole\DemandeLicence;
use App\Models\AutoEcole\DemandeAgrement;
use Illuminate\Support\Facades\Validator;
use App\Models\AutoEcole\DemandeLicenceFile;
use App\Models\AutoEcole\DemandeAgrementFile;
use App\Models\AutoEcole\DemandeAgrementRejet;



class DemandeAgrementController extends ApiController
{
    private function generateUniqueCode()
    {
        $code = rand(100000, 999999);

        while (AutoEcole::where('code', $code)->exists()) {
            $code = rand(100000, 999999);
        }

        return $code;
    }
    public function generateUniqueLicenceCode()
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
        $this->hasAnyPermission(["all", "read-agreement-managemen","edit-agreement-management"]);

        $query = DemandeAgrement::with(['promoteur', 'departement', 'commune', 'fiche']);
        // Appliquer les filtres
        $query->orderByDesc('id');
        $query = $this->applyFilters($query);
        $demandes = $query->paginate(10);
        // on initialise une collection vide pour stocker les npi
        $npiCollection = collect();

        // on itère sur chaque demande d'agrément
        foreach ($demandes as $demande) {
            // Ajoute le promoteur_npi à la collection si non nul
            if (!is_null($demande->promoteur_npi)) {
                $npiCollection->push($demande->promoteur_npi);
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
            $promoteur = $candidats->where('npi', $demande->promoteur_npi)->first();
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
            # Ajout de la raison social
            // $dgi = new Dgi($demande->ifu);
            // $demande->entreprise = $dgi->raisonSociale();
            $demande->entreprise = 'Inconnue';

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

            $query = $query->where(function ($query) use ($searchTerm) {
                $query->where('ifu', 'LIKE', "%$searchTerm%")
                    ->orWhere('auto_ecole', 'LIKE', "%$searchTerm%");
            });
        }

        return $query;
    }

    public function validateDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-agreement-management"]);

        DB::connection("base")->beginTransaction();
        $validator = Validator::make($request->all(), [
            'd_agrement_id' => "required|integer|min:1",
            'autoecole_name' => "required",
            'autoecole_email' => "required",
            'autoecole_phone' => "required",
            'autoecole_adresse' => "required",
            'num_ifu' => "required",
            'commune_id' => "required",
            'departement_id' => "required",
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Démarrez une transaction de base de données
            $da = DemandeAgrement::find($request->d_agrement_id);
            if (!$da) {
                return $this->errorResponse("La demande est introuvable");
            }
            $da_file = DemandeAgrementFile::where('demande_agrement_id', $request->d_agrement_id)->first();
            if (!$da_file) {
                return $this->errorResponse("Les fichiers de la demande sont introuvable");
            }
            // Vérifier l'unicité de l'email
            if (AutoEcole::where('email', $da->email_pro)->exists()) {

                return $this->errorResponse('Cet email est déjà utilisé pour une autre auto-école', 422);
            }
            if (AutoEcole::where('num_ifu', $da->ifu)->exists()) {

                $ae = AutoEcole::where('num_ifu', $da->ifu)->first();
                if ($ae) {
                    $promoteur = Promoteur::where('npi', $da->promoteur_npi)->first();
                    if ($promoteur && $promoteur->id != $ae->promoteur_id) {

                        return $this->errorResponse('Ce IFU est déjà utilisé par un autre promoteur', 422);
                    }
                }
            }
            $state = 'validate';
            $da->update([
                'state' => $state,
                'date_validation' => now(),

            ]);
            do {
                $randomNumber = mt_rand(0, 999999999999);
                $otp_code = str_pad($randomNumber, 12, '0', STR_PAD_LEFT);
            } while (Agrement::where('code', $otp_code)->exists());
            // Créer une nouvelle entrée dans la table "Agrements"
            $agrement = new Agrement();
            $agrement->promoteur_id = $da->promoteur_id;
            $agrement->code = $otp_code;
            $agrement->date_obtention = now();
            $agrement->demande_agrement_id = $da->id;
            $agrement->save();
            // Creer l'auto école
            $code = $this->generateUniqueCode();

            $autoecole = new AutoEcole();
            $autoecole->name = $da->auto_ecole;
            $autoecole->email = $da->email_pro;
            $autoecole->phone = $da->telephone_pro;
            $autoecole->adresse = $request->autoecole_adresse;
            $autoecole->code = $code;
            $autoecole->num_ifu = $da->ifu;
            $autoecole->commune_id = $da->commune_id;
            $autoecole->promoteur_id = $da->promoteur_id;
            $autoecole->departement_id = $da->departement_id;
            $autoecole->agrement_id = $agrement->id;
            $autoecole->slug = Str::slug($request->input('autoecole_name'));
            $autoecole->save();

            // Ajoute les moniteurs à la collection si non nuls
            $moniteurs = $da->moniteurs;
            foreach ($moniteurs as $npi) {
                Moniteur::create(
                    [
                        'npi' => $npi,
                        'auto_ecole_id' => $autoecole->id,
                        'active' => true,
                    ],
                );
            }

            $name = $request->input('autoecole_name');
            $email = $da->email_promoteur;
            Mail::to($email)->send(new AutoEcoleMail($name, $code));
            // Notifier le promoteur
            $promoteur = $da->promoteur;
            Help::historique(
                'agrement',
                'Demande d\'agrément validée',
                'demande-agrement-validate',
                'Votre demande d\'agrément a été validée avec succès',
                $promoteur,
                $da,
            );

            //creer la licence
            $demandeLicence = DemandeLicence::updateOrCreate([
                'auto_ecole_id' => $autoecole->id,
            ], [
                'promoteur_id' => $promoteur->id,
                'reference' => $agrement->code,
                'auto_ecole_id' => $autoecole->id,
                'npi' => $promoteur->npi,
                'moniteurs' => json_encode($da->moniteurs),
                "vehicules" => json_encode($da->vehicules),
                'state' => "validate"
            ]);

            $demandeLicenceFile = DemandeLicenceFile::updateOrCreate([
                'demande_licence_id' => $demandeLicence->id,
            ], [
                'demande_licence_id' => $demandeLicence->id,
                'carte_grise' => $da_file->carte_grise,
                'assurance_visite' => $da_file->assurance_visite,
                'photo_vehicules' => $da_file->photo_vehicules,
            ]);

            $licence = Licence::updateOrCreate([
                'auto_ecole_id' => $autoecole->id,
            ], [
                'auto_ecole_id' => $autoecole->id,
                'code' => $this->generateUniqueLicenceCode(),
                'date_debut' => now()->toDateString(),
                'date_fin' => now()->addYear()->toDateString(),
                'status' => true
            ]);

            Help::historique(
                'licence',
                'Licence créée',
                'demande-licence-validate',
                $message = "L'ANaTT a créé avec succès la dernière licence de votre auto-école {$request->input('autoecole_name')}.",
                $promoteur,
                $demandeLicence
            );


            $vehiculess = $demandeLicence->vehicules;
            // Enregistrer les vehicule
            foreach ($vehiculess as $vehicules) {
                Vehicule::create([
                    'immatriculation' => $vehicules['immatriculation'],
                    'auto_ecole_id' => $autoecole->id,
                ]);
            }
            $date_fin = $licence->date_fin;
            $name = $request->input('autoecole_name');
            Mail::to($email)->send(new LicenceMail($name, $date_fin));
            // Notifier le promoteur
            $promoteur = $demandeLicence->promoteur;
            $autoecole = $demandeLicence->autoecole;
            // Confirmez la transaction si tout s'est bien passé
            DB::connection("base")->commit();
            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::connection("base")->rollBack();
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation');
        }
    }

    public function rejectDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-agreement-management"]);

        $validator = Validator::make($request->all(), [
            'd_agrement_id' => "required|integer|min:1",
            'consigne' => "max:5000",
            "motif" => "required"
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            $da = DemandeAgrement::find($request->d_agrement_id);
            if (!$da) {
                return $this->errorResponse("La demande est introuvable");
            }
            $promoteur = $da->promoteur;
            $state = 'rejected';
            $da->update([
                'state' => $state,
                'date_rejet' => now(),

            ]);

            // Créer une nouvelle entrée dans la table "DemandeAgrementRejet"
            $demandeAgrementRejet = new DemandeAgrementRejet();
            $demandeAgrementRejet->demande_agrement_id = $da->id;
            $demandeAgrementRejet->motif = $request->motif;
            $demandeAgrementRejet->state = 'init';
            $demandeAgrementRejet->save();

            // Notifier le promoteur
            Help::historique(
                'agrement',
                'Demande d\'agrément rejetée',
                'demande-agrement-rejected',
                'Votre demande d\'agrément a été rejetée pour le motif suivant : ' . $request->motif . '. Consigne : ' . $request->consigne,
                $promoteur,
                $demandeAgrementRejet,
            );

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet', 500);
        }
    }
}
