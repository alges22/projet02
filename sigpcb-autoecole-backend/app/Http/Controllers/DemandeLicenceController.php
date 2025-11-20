<?php

namespace App\Http\Controllers;

use App\Services\Help;
use App\Models\Vehicule;
use App\Models\AutoEcole;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\DemandeLicence;
use App\Models\DemandeLicenceFile;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\AnattMoniteur;
use App\Models\DemandeLicenceRejet;
use Illuminate\Support\Facades\Validator;

class DemandeLicenceController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        if (!auth()->check()) {
            return $this->errorResponse("Vous devez être connecté en tant que promoteur pour demander une licence", statuscode: 403);
        }
        try {

            $v = Validator::make($request->all(), [
                'auto_ecole_id' => 'required|integer|exists:auto_ecoles,id',
                "moniteurs" => "required",
                "vehicules" => "required",
                'carte_grise' => "required|array",
                'assurance_visite' => "required|array",
                'photo_vehicules' => "required|array",
                'carte_grise.*' => "required|file|mimes:pdf,jpg,png",
                'assurance_visite.*' => "required|file|mimes:pdf,jpg,png",
                'photo_vehicules.*' => "required|file|mimes:pdf,jpg,png",
            ]);

            if ($v->fails()) {
                return $this->errorResponse("La validation a échoué", $v->errors(), statuscode: 422);
            }

            $immatriculations = json_decode($request->vehicules, true);
            if (!$immatriculations) {
                return $this->errorResponse("Veuillez sélectionner au moins un véhicule", $v->errors(), statuscode: 422);
            }
            $immatriculations = array_map(function ($item) {
                return ['immatriculation' => preg_replace('/\s+/', '', $item['immatriculation'])];
            }, $immatriculations);
            foreach ($immatriculations as $key => $immatriculation) {
                if (Vehicule::where("immatriculation", $immatriculation['immatriculation'])->first()) {
                    return  $this->errorResponse("L'immatriculation {$immatriculation['immatriculation']} est déjà prise");
                }
            }

            $moniteurs = json_decode($request->moniteurs, true);
            if (!$moniteurs) {
                return $this->errorResponse("Veuillez sélectionner au moins deux moniteurs", $v->errors(), statuscode: 422);
            }

            foreach ($moniteurs as $key => $npi) {
                if (!AnattMoniteur::whereNpi($npi)->first()) {
                    return $this->errorResponse("Le moniteur ayant le NPI $npi n'existe pas dans la base de l'ANaTT.", statuscode: 422);
                }
            }
            $ae = AutoEcole::find($request->auto_ecole_id);
            $promoteur = $ae->promoteur;

            if ($ae->hasLicence()) {
                return $this->errorResponse('Vous avez déjà une licence en cours pour cette auto-école');
            }

            if ($promoteur->id !== auth()->id()) {
                return $this->errorResponse("Vous ne pouvez pas demander de licence pour cette auto-école", statuscode: 403);
            }
            $agrement = $ae->agrement;

            $data = $request->all();
            $data['vehicules'] = json_encode($immatriculations);
            $data['reference'] = $agrement->code;
            $data['promoteur_id'] = $promoteur->id;
            $data['npi'] = $promoteur->npi;
            $data['date_validation'] = now();
            $demande =  DemandeLicence::create($data);

            $this->storeFiches($request, $demande);
            Help::historique(
                'licence',
                "Demande de licence effectuée",
                'demande-licence-init',
                "Votre demande de licence a été envoyée avec succès, et est en attente de validation",
                $promoteur,
                $demande
            );
            DB::commit();
            return $this->successResponse($demande, "Votre demande a été envoyée avec succès, et est en attente de validation");
        } catch (\Throwable $th) {
            DB::rollBack();
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la demande");
        }
    }

    private function storeFiches(Request $request, DemandeLicence $demande)
    {
        $fiches = [
            'carte_grise',
            'assurance_visite',
            'photo_vehicules',
        ];

        $data = [
            'demande_licence_id' => $demande->id
        ];
        foreach ($fiches as $fiche_name) {
            if ($request->hasFile($fiche_name)) {
                $fiche = $request->file($fiche_name);
                if (is_array($fiche)) {
                    $paths = [];
                    foreach ($fiche as $key => $fic) {
                        $paths[] = $fic->store("fiches", "public");
                    }

                    $path = json_encode($paths);
                }
                $data[$fiche_name] = $path;
            }
        }

        return DemandeLicenceFile::updateOrCreate([
            'demande_licence_id' => $demande->id
        ], $data);
    }


    public function rejets($demandeRejet)
    {
        try {
            $demandeRejet =  DemandeLicenceRejet::find($demandeRejet);

            if (!$demandeRejet) {
                return $this->errorResponse("Ce rejet de demande de licence est introuvable", statuscode: 404);
            }
            /**
             * @var \App\Models\DemandeLicence $demande
             */
            $demande = $demandeRejet->demandeLicence;

            $demande->load('fiche');
            $demande->load('autoEcole');

            $moniteurs = GetCandidat::get(json_decode($demande->moniteurs, true));
            $demande->setAttribute('monitors', $moniteurs);
            return $this->successResponse($demande);
        } catch (\Throwable $th) {
            logger()->error($th);

            return $this->errorResponse("Une erreur s'est produite lors de la récupération du rejet");
        }
    }

    public function update(Request $request, $demandeRejet)
    {

        $v = Validator::make($request->all(), [
            'demande_rejet_id' => 'required|integer|exists:demande_licence_rejets,id',
            "moniteurs" => "required",
            "vehicules" => "required",
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors(), statuscode: 422);
        }
        DB::beginTransaction();
        try {
            if ($demandeRejet != $request->demande_rejet_id) {
                return $this->errorResponse("Ce rejet de demande de licence est introuvable", statuscode: 404);
            }

            $demandeRejet =  DemandeLicenceRejet::find($demandeRejet);
            $data = $v->validate();

            unset($data['demande_rejet_id']);
            /**
             * @var \App\Models\DemandeLicence $demande
             */
            $demande = $demandeRejet->demandeLicence;

            $data['state'] = "pending";
            if ($demande->promoteur_id != auth()->id()) {
                return $this->errorResponse("Vous n'avez pas les autorisations nécessaire pour modifier cette demande", statuscode: 403);
            }
            $demande->update($data);

            $demande->load('promoteur');

            $demandeRejet->update([
                'state' => 'pending',
                'date_correction' => now()
            ]);

            $this->storeFiches($request, $demande);

            Help::historique(
                'licence',
                'Correction de rejet de licence envoyée',
                'demande-licence-pending',
                $message = "La correction de votre demande  de licence a été envoyée avec succès, et est en attente de validation",
                $demande->promoteur,
                $demandeRejet
            );
            DB::commit();
            return $this->successResponse($demande, $message);
        } catch (\Throwable $th) {
            DB::rollBack();
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la demande");
        }
    }
}
