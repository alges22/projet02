<?php

namespace App\Http\Controllers;

use App\Services\Dgi;
use Carbon\Carbon;
use App\Services\Api;
use App\Services\Help;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\AutoEcole\Licence;
use App\Models\AutoEcole\Agrement;
use App\Models\AutoEcole\Moniteur;
use App\Models\AutoEcole\Vehicule;
use Illuminate\Support\Facades\DB;
use App\Mail\AutoEcoleStatusUpdate;
use App\Models\AutoEcole\AutoEcole;
use App\Models\AutoEcole\Promoteur;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Models\AutoEcole\DemandeLicence;
use App\Models\AutoEcole\DemandeAgrement;
use App\Models\Moniteur as AnattMoniteur;
use Illuminate\Support\Facades\Validator;
use App\Models\AutoEcole\AutoEcoleInactive;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;



class AutoEcoleController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["read-driving-school-management","edit-driving-school-management", "all"]);
        // Récupérer les auto-écoles avec les relations
        $query = AutoEcole::with(['departement', 'commune', 'promoteur', 'agrement', 'vehicules']);
        $query->orderByDesc('id');
        $query = $this->applyFilters($query);
        $ae = $query->paginate(10);

        // on initialise une collection vide pour stocker les npi
        $npiCollection = collect();

        // Parcourir les auto-écoles pour récupérer les licences, les moniteurs, et les NPI
        foreach ($ae as $autoecole) {
            // Récupérer les licences pour cette auto-école
            $licences = Licence::where('auto_ecole_id', $autoecole->id)->get();

            // Récupérer les moniteurs pour cette auto-école
            $moniteurs = Moniteur::where('auto_ecole_id', $autoecole->id)->get();

            // Ajouter les licences à l'objet AutoEcole
            $autoecole->licences = $licences;

            // Ajouter les moniteurs à l'objet AutoEcole
            $autoecole->moniteurs = $moniteurs;

            // Ajouter le NPI du promoteur à la collection
            $promoteurNPI = $autoecole->promoteur->npi;
            $npiCollection->push($promoteurNPI);

            // Ajouter les NPI des moniteurs à la collection
            foreach ($autoecole->moniteurs as $moniteur) {
                $moniteurNPI = $moniteur->npi;
                $npiCollection->push($moniteurNPI);
            }
        }

        // Retirer les valeurs nulles ou vides de la collection
        $npiCollection = $npiCollection->filter(function ($npi) {
            return !is_null($npi) && $npi !== '';
        });

        // Retirer les doublons de la collection
        $npiCollection = $npiCollection->unique();

        // Obtient les candidats en fonction des valeurs de npi
        $candidats = collect(GetCandidat::get($npiCollection->all()));

        // Associer les informations des candidats aux auto-écoles
        foreach ($ae as $autoecole) {
            // Associer le promoteur
            $promoteur = $candidats->where('npi', $autoecole->promoteur->npi)->first();
            $autoecole->promoteur_info = $promoteur;

            // Associer les moniteurs
            $moniteursInfo = [];
            foreach ($autoecole->moniteurs as $moniteur) {
                $moniteurInfo = $candidats->where('npi', $moniteur->npi)->first();
                if ($moniteurInfo) {
                    $moniteursInfo[] = $moniteurInfo;
                }
            }
            $autoecole->moniteurs_info = $moniteursInfo;
            # Ajout de la raison social
            // $dgi = new Dgi($autoecole->num_ifu);
            // $autoecole->entreprise = $dgi->raisonSociale();
            $autoecole->entreprise = 'Inconnue';
        }

        return $this->successResponse($ae);
    }

    public function getRaisonSocial(string $ifu)
    {
        $this->hasAnyPermission(["read-driving-school-management", "edit-driving-school-management", "all"]);

        // Validation de l'IFU
        if (empty($ifu) || strlen($ifu) !== 13 || !is_numeric($ifu)) {
            return $this->errorResponse('IFU invalide. Veuillez fournir un IFU valide de 13 chiffres.', 422);
        }

        try {
            // Vérifier d'abord dans le cache
            $cacheKey = "raison_sociale_ifu_{$ifu}";
            $raisonSociale = Cache::get($cacheKey);

            if (!$raisonSociale) {
                // Si pas en cache, faire l'appel à l'API DGI
                $dgi = new Dgi($ifu);
                $raisonSociale = $dgi->raisonSociale();

                // Mettre en cache pour 24 heures si la réponse est valide
                if ($raisonSociale) {
                    Cache::put($cacheKey, $raisonSociale, now()->addHours(24));
                }
            }

            if (empty($raisonSociale)) {
                return $this->errorResponse("Aucune raison sociale trouvée pour l'IFU: {$ifu}", 404);
            }

            return $this->successResponse([
                'ifu' => $ifu,
                'raison_sociale' => $raisonSociale,
                // 'source' => Cache::has($cacheKey) ? 'cache' : 'api'
            ]);

        } catch (\Throwable $th) {
            logger()->error($th);
            // Gérer les exceptions avec une réponse d'erreur
            return $this->errorResponse('Une erreur est survenue', 500);
        }
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
        if (request()->has('status')) {
            $status = request('status');

            if ($status === 'all') {
                // Ne pas appliquer de filtre
            } else {
                $query = $query->where('status', $status);
            }
        }
        // Filtre par recherche
        if (request()->has('search')) {
            $searchTerm = request('search');

            $query = $query->where(function ($query) use ($searchTerm) {
                $query->where('num_ifu', 'LIKE', "%$searchTerm%")
                    ->orWhere('name', 'LIKE', "%$searchTerm%");
            });
        }
        return $query;
    }

    public function updateAEStatus(Request $request)
    {
        $this->hasAnyPermission(["edit-driving-school-management", "all"]);
        $validator = Validator::make($request->all(), [
            'auto_ecole_id' => "required|integer",
            'motif' => 'required_if:status,false',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échoué', $validator->errors()->toArray(), 422);
        }

        try {
            $autoecoleId = $request->input('auto_ecole_id');
            $motif = $request->input('motif');
            $status = $request->input('status');

            // Rechercher la licence par ID
            $autoecole = AutoEcole::find($autoecoleId);

            // Vérifier si la licence a été trouvée
            if (!$autoecole) {
                return $this->errorResponse('AutoEcole introuvable', 404);
            }
            $email = $autoecole->email;

            // Mettre à jour le champ status
            $autoecole->status = $status;
            $autoecole->save();

            if ($status == false) {
                // Créer une nouvelle entrée dans la table "Agrements"
                $aeinactive = new AutoEcoleInactive();
                $aeinactive->auto_ecole_id = $autoecoleId;
                $aeinactive->motif = $motif;
                $aeinactive->date_action = now();
                $aeinactive->save();
            }
            // Envoyer un courriel en fonction du statut
            Mail::to($email)->send(new AutoEcoleStatusUpdate($status, $motif));

            // Retourner une réponse de succès
            return $this->successResponse('AutoEcole mise à jour avec succès');
        } catch (\Throwable $th) {
            logger()->error($th);
            // Gérer les exceptions avec une réponse d'erreur
            return $this->errorResponse('Une erreur est survenue', 500);
        }
    }

    private function generateUniqueCode()
    {
        $code = rand(100000, 999999);

        while (AutoEcole::where('code', $code)->exists()) {
            $code = rand(100000, 999999);
        }

        return $code;
    }
    /**
     * Faire tout le code d'importation ici
     *
     */
    public function createAE(Request $request)
    {
        $this->hasAnyPermission(["edit-driving-school-management", "all"]);

        DB::connection("base")->beginTransaction();
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'name' => "required",
            'num_ifu' => 'required',
            'phone' => ['required', 'regex:/^[0-9]+$/'],
            'email' => 'required|email',
            'adresse' => 'required',
            'vehicules' => 'required',
            'departement_id' => 'required|integer',
            'commune_id' => 'required|integer',
            'npi' => 'required',
            'email_promoteur' => 'required|email',
            'agrement_code' => 'required',
            'licence_code' => 'required',
            'date_licence' => 'required',
            'moniteurs' => 'required',
            'type' => 'required|in:civil,militaire',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échoué', $validator->errors()->toArray(), 422);
        }

        try {
            $dgi = new Dgi($request->input('num_ifu'));
            if (!$dgi->exists()) {
                return $this->errorResponse("Ce numéro IFU n'existe pas chez la DGI", 422);
            }
            if (!$dgi->raisonSociale()) {
                return $this->errorResponse("Le numéro IFU ne disponse pas de raison sociale.");
            }

            $moniteurs = explode(',', $request->input('moniteurs'));
            $moniteurs = array_map(function ($item) {
                return preg_replace('/[^0-9]/', '', trim($item));
            }, $moniteurs);

            foreach ($moniteurs as $moniteur) {
                $response = Api::base('GET', "candidats/" . $moniteur);

                if (!$response->successful()) {
                    return $this->errorResponse("Le numéro NPI du moniteur $moniteur n'existe pas.", 422);
                }
            }

            $npiPromotor = preg_replace('/[^0-9]/', '', $request->input('npi'));
            $responseP = Api::base('GET', "candidats/" . $npiPromotor);

            if (!$responseP->successful()) {
                return $this->errorResponse("Le numéro NPI du promoteur n'existe pas.", 422);
            }

            $vehicules = explode(',', $request->input('vehicules'));
            $vehicules = array_map(function ($item) {
                return preg_replace('/[^0-9]/', '', trim($item));
            }, $vehicules);

            foreach ($vehicules as $vehicule) {
                $vehiculeExists =  Vehicule::where('immatriculation', $vehicule)->first();
                if ($vehiculeExists) {
                    return $this->errorResponse("L'immatriculation $vehicule est déjà associé a une autre auto école.", 422);
                }
            }

            $dateLicence = Carbon::parse($request->input('date_licence'));
            $today = Carbon::now();

            if ($dateLicence->diffInDays($today) > 365) {
                return $this->errorResponse("La date de licence ne peut pas dépasser un an à partir de la date actuelle", 422);
            }

            $aenameExists =  DemandeAgrement::where('auto_ecole', $request->input('name'))->first();

            if ($aenameExists) {
                return $this->errorResponse("Ce nom d'auto école est déjà pris", 422);
            }

            $clicenceExists =  Licence::where('code', $request->input('licence_code'))->first();

            if ($clicenceExists) {
                return $this->errorResponse("Ce code de licence est déjà pris ", 422);
            }

            $cagrementExists =  Agrement::where('code', $request->input('agrement_code'))->first();

            if ($cagrementExists) {
                return $this->errorResponse("Ce code d'agrément est déjà pris ", 422);
            }

            $demandeExists =  DemandeAgrement::where('ifu', $request->input('num_ifu'))->where('auto_ecole', '!=', $request->input('name'))->first();

            if ($demandeExists) {
                return $this->errorResponse("Ce numéro IFU est déjà pris ", 422);
            }
            if (count($moniteurs) < 2) {
                return $this->errorResponse("Les moniteurs de l'auto-école n'atteignent pas 2 ou peut-être leurs NPIs sont mal renseignés", 422);
            }
            $emailExists = AutoEcole::where('email', $request->input('email'))
                ->where('name', '!=', $request->input('name'))
                ->first();

            if ($emailExists) {
                return $this->errorResponse("Cet email est déjà pris", 422);
            }
            $promoteur = Promoteur::where('npi', $npiPromotor)->first();

            if (!$promoteur) {
                $existMail = Promoteur::where('email', $request->get('email_promoteur'))->first();
                if ($existMail) {
                    return $this->errorResponse("Cette adresse e-mail est déjà prise par un autre promoteur d'auto école", 422);
                }

                $demandeExists =  DemandeAgrement::where('ifu', $request->input('num_ifu'))->where('auto_ecole', '!=', $request->input('name'))->first();

                if ($demandeExists) {
                    return $this->errorResponse("Ce numéro IFU est déjà pris ", 422);
                }

                $slug = Str::slug($request->input('name'));
                $aeslugExists = AutoEcole::where('slug', $slug)->first();
                if ($aeslugExists) {
                    return $this->errorResponse("Ce nom d'auto école est déjà pris", 422);
                }

                $promoteur = Promoteur::updateOrCreate(
                    ["npi" => $npiPromotor],
                    [
                        'email' => $request->input('email_promoteur'),
                        'npi' => $npiPromotor,
                    ]
                );

                $demandeAgrement = DemandeAgrement::updateOrCreate([
                    'auto_ecole' => $request->input('name'),
                    'ifu' => $request->input('num_ifu'),
                ], [
                    'auto_ecole' => $request->input('name'),
                    'promoteur_npi' => $npiPromotor,
                    'departement_id' => $request->input('departement_id'),
                    'commune_id' => $request->input('commune_id'),
                    'moniteurs' => json_encode($moniteurs),
                    'telephone_pro' => $request->input('phone'),
                    'email_pro' => $request->input('email'),
                    'email_promoteur' => $request->input('email_promoteur'),
                    'promoteur_id' => $promoteur->id,
                    'state' => "validate"
                ]);

                Help::historique(
                    'agrement',
                    'Agrément créé',
                    'demande-agrement-validate',
                    $message = "L'ANaTT a créé avec succès votre agrément de votre auto-école {$request->input('name')}.",
                    $promoteur,
                    $demandeAgrement
                );
                $dateObtentionAgrement = now();
                $agrement = Agrement::updateOrCreate([
                    'demande_agrement_id' => $demandeAgrement->id,
                ], [
                    'promoteur_id' => $promoteur->id,
                    'date_obtention' => $dateObtentionAgrement,
                    'code' => $request->input('agrement_code'),
                    'demande_agrement_id' => $demandeAgrement->id,
                ]);

                $code = $this->generateUniqueCode();

                $autoEcole = AutoEcole::updateOrCreate([
                    'agrement_id' => $agrement->id,
                ], [
                    'agrement_id' => $agrement->id,
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                    'code' => $code,
                    'slug' => $slug,
                    'num_ifu' => $request->input('num_ifu'),
                    'promoteur_id' => $promoteur->id,
                    'departement_id' => $request->input('departement_id'),
                    'commune_id' => $request->input('commune_id'),
                    'adresse' => $request->input('adresse'),
                    'imported' => false,
                    'type' => $request->input('type')
                ]);

                foreach ($moniteurs as $key => $npi) {
                    Moniteur::updateOrCreate([
                        'auto_ecole_id' => $autoEcole->id,
                        'npi' => $npi
                    ], [
                        'auto_ecole_id' => $autoEcole->id,
                        'npi' => $npi,
                        'active' => true
                    ]);
                    $existing = AnattMoniteur::whereNpi($npi)->first();
                    if (!$existing) {
                        AnattMoniteur::create([
                            "npi" => $npi,
                            "agent_id" => auth()->id(),
                            "date_validation" => now(),
                        ]);
                    }
                }

                $vehicule_tab = $vehicules;

                $vehicules = array_map(function ($str) {
                    return [
                        'immatriculation' => $str,
                    ];
                }, $vehicule_tab);

                $demandeLicence = DemandeLicence::updateOrCreate([
                    'auto_ecole_id' => $autoEcole->id,
                ], [
                    'promoteur_id' => $promoteur->id,
                    'reference' => $agrement->code,
                    'auto_ecole_id' => $autoEcole->id,
                    'npi' => $promoteur->npi,
                    'moniteurs' => json_encode($moniteurs),
                    "vehicules" => json_encode($vehicules),
                    'state' => "validate"
                ]);

                Help::historique(
                    'licence',
                    'Licence créée',
                    'demande-licence-validate',
                    $message = "L'ANaTT a créé avec succès la dernière licence de votre auto-école {$request->input('name')}.",
                    $promoteur,
                    $demandeLicence
                );

                Licence::updateOrCreate([
                    'auto_ecole_id' => $autoEcole->id,
                ], [
                    'auto_ecole_id' => $autoEcole->id,
                    'code' => $request->input('licence_code'),
                    'date_debut' => $dateLicence->copy()->addYears(-1),
                    'date_fin' => $dateLicence,
                    'status' => true
                ]);

                $vehiculess = $demandeLicence->vehicules;
                foreach ($vehiculess as $vehicules) {
                    Vehicule::create([
                        'immatriculation' => $vehicules['immatriculation'],
                        'auto_ecole_id' => $autoEcole->id,
                    ]);
                }
                DB::connection("base")->commit();
                DB::commit();
                return $this->successResponse($autoEcole);
            }

            $demandeExists =  DemandeAgrement::where('ifu', $request->input('num_ifu'))->where('auto_ecole', '!=', $request->input('name'))->first();

            if ($demandeExists) {
                return $this->errorResponse("Ce numéro IFU est déjà pris ", 422);
            }
            $demandeAgrement = DemandeAgrement::updateOrCreate([
                'auto_ecole' => $request->input('name'),
                'ifu' => $request->input('num_ifu'),
            ], [
                'auto_ecole' => $request->input('name'),
                'promoteur_npi' => $npiPromotor,
                'departement_id' => $request->input('departement_id'),
                'commune_id' => $request->input('commune_id'),
                'moniteurs' => json_encode($moniteurs),
                'telephone_pro' => $request->input('phone'),
                'email_pro' => $request->input('email'),
                'email_promoteur' => $request->input('email_promoteur'),
                'promoteur_id' => $promoteur->id,
                'state' => "validate"
            ]);

            Help::historique(
                'agrement',
                'Agrément créé',
                'demande-agrement-validate',
                $message = "L'ANaTT a créé avec succès votre agrément de votre auto-école {$request->input('name')}.",
                $promoteur,
                $demandeAgrement
            );
            $dateObtentionAgrement = now();
            $agrement = Agrement::updateOrCreate([
                'demande_agrement_id' => $demandeAgrement->id,
            ], [
                'promoteur_id' => $promoteur->id,
                'date_obtention' => $dateObtentionAgrement,
                'code' => $request->input('agrement_code'),
                'demande_agrement_id' => $demandeAgrement->id,
            ]);

            $emailExists = AutoEcole::where('email', $request->input('email'))
                ->where('name', '!=', $request->input('name'))
                ->first();

            if ($emailExists) {
                return $this->errorResponse("Cet email est déjà pris", 422);
            }
            $code = $this->generateUniqueCode();
            $slug = Str::slug($request->input('name'));

            $autoEcole = AutoEcole::updateOrCreate([
                'agrement_id' => $agrement->id,
            ], [
                'agrement_id' => $agrement->id,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'code' => $code,
                'slug' => $slug,
                'num_ifu' => $request->input('num_ifu'),
                'promoteur_id' => $promoteur->id,
                'departement_id' => $request->input('departement_id'),
                'commune_id' => $request->input('commune_id'),
                'adresse' => $request->input('adresse'),
                'imported' => false,
                'type' => $request->input('type'),
            ]);

            foreach ($moniteurs as $key => $npi) {
                Moniteur::updateOrCreate([
                    'auto_ecole_id' => $autoEcole->id,
                    'npi' => $npi
                ],[
                    'auto_ecole_id' => $autoEcole->id,
                    'npi' => $npi,
                    'active' => true
                ]);
                $existing = AnattMoniteur::whereNpi($npi)->first();
                if (!$existing) {
                    AnattMoniteur::create([
                        "npi" => $npi,
                        "agent_id" => auth()->id(),
                        "date_validation" => now(),
                    ]);
                }
            }

            $vehicule_tab = $vehicules;

            $vehicules = array_map(function ($str) {
                return [
                    'immatriculation' => $str,
                ];
            }, $vehicule_tab);

            $demandeLicence = DemandeLicence::updateOrCreate([
                'auto_ecole_id' => $autoEcole->id,
            ], [
                'promoteur_id' => $promoteur->id,
                'reference' => $agrement->code,
                'auto_ecole_id' => $autoEcole->id,
                'npi' => $promoteur->npi,
                'moniteurs' => json_encode($moniteurs),
                "vehicules" => json_encode($vehicules),
                'state' => "validate"
            ]);

            Help::historique(
                'licence',
                'Licence créée',
                'demande-licence-validate',
                $message = "L'ANaTT a créé avec succès la dernière licence de votre auto-école {$request->input('name')}.",
                $promoteur,
                $demandeLicence
            );

            Licence::updateOrCreate([
                'auto_ecole_id' => $autoEcole->id,
            ], [
                'auto_ecole_id' => $autoEcole->id,
                'code' => $request->input('licence_code'),
                'date_debut' => $dateLicence->copy()->addYears(-1),
                'date_fin' => $dateLicence,
                'status' => true
            ]);

            $vehiculess = $demandeLicence->vehicules;
            foreach ($vehiculess as $vehicules) {
                Vehicule::create([
                    'immatriculation' => $vehicules['immatriculation'],
                    'auto_ecole_id' => $autoEcole->id,
                ]);
            }

            DB::connection("base")->commit();
            DB::commit();
            return $this->successResponse($autoEcole);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::connection("base")->rollback();
            DB::rollback();
            return $this->errorResponse("Une erreur s'est produite", 422);
        }
    }

    public function updateAE(Request $request, $id)
    {
        $this->hasAnyPermission(["edit-driving-school-management", "all"]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes',
                'email' => 'sometimes|email',
                'phone' => 'sometimes|min:8|max:13',
                'adresse' => 'sometimes',
                'commune_id' => 'sometimes',
                'departement_id' => 'sometimes',
                'num_ifu' => 'sometimes|required',
                'type' => 'required|in:civil,militaire',

            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }

            # Vérification de l'IFU
            $dgi = new Dgi($request->input('num_ifu'));
            if (!$dgi->exists()) {
                return $this->errorResponse("Ce numéro IFU n'existe pas chez la DGI", 422);
            }
            if (!$dgi->raisonSociale()) {
                return $this->errorResponse("Le numéro IFU ne disponse pas de raison sociale.");
            }
            try {
                $auto_ecole = AutoEcole::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'auto-école demandée est introuvable');
            }
            $slug = Str::slug($request->input('name'));
            $aeslugExists = AutoEcole::where('slug', $slug)
                ->where('id', '!=', $id)
                ->first();

            if ($aeslugExists) {
                return $this->errorResponse("Ce nom d'auto école est déjà pris", 422);
            }
            // Vérification de l'existence de l'e-mail, en excluant la ligne actuelle
            $existingEmail = AutoEcole::where('email', $request->input('email'))
                ->where('id', '!=', $id)
                ->first();

            if ($existingEmail) {
                return $this->errorResponse("Cet e-mail est déjà pris par une autre auto-école");
            }

            $oldname = $auto_ecole->name;
            $input = $request->all();
            $input['slug'] = $slug;
            $auto_ecole->update($input);

            //modifier les informations de l'auto école dans la demande d'agrement
            $da = DemandeAgrement::where('auto_ecole', $oldname)->first();
            $da->update([
                'auto_ecole' => $request->input('name'),
                'ifu' => $request->input('num_ifu'),
            ]);
            return $this->successResponse($auto_ecole);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour de l\'auto-école');
        }
    }

    public function updateAEMoniteur(Request $request, $id)
    {
        $this->hasAnyPermission(["edit-driving-school-management", "all"]);

        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'auto_ecole_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }

            try {
                $moniteur = Moniteur::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le moniteur demandé est introuvable');
            }
            $existingMoniteur = Moniteur::where('npi', $request->input('npi'))
                ->where('auto_ecole_id', $request->input('auto_ecole_id'))
                ->where('active', true)
                ->first();

            if ($existingMoniteur) {
                return $this->errorResponse("Ce moniteur est déjà associé à cette auto-école");
            }
            $input = $request->all();
            $moniteur->update($input);

            return $this->successResponse($moniteur);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du npi du moniteur');
        }
    }

    public function updateAEPromoteur(Request $request, $id)
    {
        $this->hasAnyPermission(["edit-driving-school-management", "all"]);

        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'auto_ecole_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }

            try {
                $promoteur = Promoteur::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le promoteur demandé est introuvable');
            }

            $existingPromoteur = Promoteur::where('npi', $request->input('npi'))->first();

            if ($existingPromoteur) {
                return $this->errorResponse("Ce promoteur existe déjà");
            }
            $oldnpi = $promoteur->npi;
            $input = $request->all();
            unset($input['auto_ecole_id']);
            $promoteur->update($input);

            $npiPromoteur = DemandeAgrement::where('promoteur_npi', $oldnpi)->first();
            $npiPromoteur->update([
                'promoteur_npi' => $request->input('npi'),
            ]);

            return $this->successResponse($promoteur);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du npi du promoteur');
        }
    }
    public function updateAEVehicule(Request $request, $id)
    {
        $this->hasAnyPermission(["edit-driving-school-management", "all"]);

        try {
            $validator = Validator::make($request->all(), [
                'immatriculation' => 'required',
                'auto_ecole_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }

            try {
                $vehicule = Vehicule::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le vehicule demandé est introuvable');
            }
            $existingvh = Vehicule::where('immatriculation', $request->input('immatriculation'))
                ->where('auto_ecole_id', $request->input('auto_ecole_id'))
                ->first();

            if ($existingvh) {
                return $this->errorResponse("Ce vehicule est déjà associé à cette auto-école");
            }
            $input = $request->all();
            $vehicule->update($input);

            return $this->successResponse($vehicule);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du npi du vehicule');
        }
    }
}
