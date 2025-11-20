<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Help;
use App\Models\Service;
use App\Models\Vehicule;
use App\Models\AutoEcole;
use Illuminate\Support\Str;
use App\Models\PromoteurIfu;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\DemandeAgrement;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\AnattMoniteur;
use App\Models\DemandeAgrementFile;
use App\Models\DemandeAgrementRejet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AgrementFormRequest;

class DemandeAgrementController extends ApiController
{

    public function store(AgrementFormRequest $request)
    {
        $oldDemande =  DemandeAgrement::where($request->only(['auto_ecole']))->first();
        if ($oldDemande) {
            if ($oldDemande->state == "payment") {
                DemandeAgrementFile::where('demande_agrement_id', $oldDemande->id)->delete();
                $oldDemande->delete();
            }
        }

        DB::beginTransaction();
        try {
            if (!$this->ifuWasVerified($request->get('npi'), $request->get('ifu'))) {
                return $this->errorResponse("Vous devez vérifier l'IFU avant de continuer");
            }
            try {
                GetCandidat::findOne($request->get('npi'));
            } catch (\Throwable $th) {
                return $this->errorResponse("Le NPI {$request->get('npi')} du promoteur est n'est pas trouvé chez l'ANIP", statuscode: 422);
            }
            $data = $request->all();

            $data['auto_ecole'] = trim($request->auto_ecole);
            $data["promoteur_npi"] = $request->npi;
            $data["state"] = "payment";

            // Rechercher un utilisateur par NPI ou par e-mail
            $promoteur = User::where('npi', $request->get('npi'))
                ->orWhere('email', $request->get('email_promoteur'))
                ->first();

            // Vérifier si l'utilisateur existe
            if (!$promoteur) {
                // Créer un nouvel utilisateur s'il n'existe pas
                $promoteur = User::create([
                    'npi' => $request->npi,
                    'email' => $request->email_promoteur
                ]);
            } else {
                if ($promoteur->npi != $request->get('npi') || $promoteur->email != $request->get('email_promoteur')) {
                    return $this->errorResponse("Le NPI ou l'e-mail du promoteur est déjà pris", statuscode: 422);
                }
            }

            $aeExisting = AutoEcole::where('num_ifu', $request->get('ifu'))->first();
            if ($aeExisting) {
                if (!$promoteur->is($aeExisting->promoteur)) {
                    return $this->errorResponse("Le numéro IFU est déjà pris");
                }
            };

            $moniteurs = $request->moniteurs;
            $immatriculations = $request->vehicules;

            foreach ($moniteurs as $key => $npi) {
                if (!AnattMoniteur::whereNpi($npi)->first()) {
                    return $this->errorResponse("Le moniteur ayant le NPI $npi n'existe pas dans la base de l'ANaTT.", statuscode: 422);
                }
            }

            foreach ($immatriculations as $key => $immatriculation) {
                if (Vehicule::where("immatriculation", preg_replace('/\s+/', '', $immatriculation))->first()) {
                    return  $this->errorResponse("L'immatriculation $immatriculation est déjà prise");
                }
            }

            $data['moniteurs'] = json_encode($moniteurs);
            $data['vehicules'] = json_encode(array_map(function ($item) {
                return ['immatriculation' => preg_replace('/\s+/', '', $item)];
            }, $immatriculations));

            # Création de l'auto-école
            $demande = $promoteur->demandes()->create($data);

            $this->storeFiches($request, $demande);
            DB::commit();
            return $this->successResponse($demande,);
        } catch (\Throwable $th) {
            DB::rollBack();
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la demande");
        }
    }


    public function submitDemande(Request $request)
    {
        $v = Validator::make($request->all(), [
            'demande_id' => 'required|exists:demande_agrements,id',
            'transaction' => 'required',
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors(), statuscode: 422);
        }

        try {

            $data = $request->all();

            $demande = DemandeAgrement::find($request->demande_id);

            $transaction = json_decode($request->transaction, true);

            if ($transaction['status'] != "approved") {
                Help::historique(
                    'agrement', /* Le service*/
                    'Demande d\'agrément échoué',/* Le titre*/
                    'demande-agrement-payment',/* L'action*/
                    "L'envoie de votre demande a échoué. Le paiement n'a pas été effectué ou une erreur s'est peut-être produite. ", /* L'action*/
                    $demande->promoteur,/* Le promoteur  concerné */
                    $demande/* Le model concerné */
                );
                return $this->errorResponse("L'envoie de votre demande a échoué. Le paiement n'a pas été effectué ou une erreur s'est peut-être produite. ");
            }

            $paymentData = [
                'transactionId' => $transaction['id'],
                'montant' => $transaction['montant'],
                'phone' =>  $transaction['phone'],
                'status' => $transaction['status'],
                'operateur' => $transaction['operateur'],
            ];

            $this->storeFiches($request, $demande);

            $transactionId = $transaction['id'];
            $fedaPayEnv = env('FEDAPAY_ENV', 'sandbox');
            if ($fedaPayEnv === 'live') {
                \FedaPay\FedaPay::setEnvironment('live');
                \FedaPay\FedaPay::setApiKey(env('FEDAPAY_LIVE_PRIVATE_KEY'));
            } else {
                \FedaPay\FedaPay::setEnvironment('sandbox');
                \FedaPay\FedaPay::setApiKey(env('FEDAPAY_SANDBOX_PRIVATE_KEY'));
            }
            $transaction = \FedaPay\Transaction::retrieve($transactionId);
            if ($transaction->status != "approved") {
                logger()->error(json_encode($paymentData));
                return $this->errorResponse('Fedapay indique que le paiement a échoué.');
            }

            if ($transaction->amount != Service::demandeAgrementAmount()) {
                logger()->error(json_encode($paymentData));
                return $this->errorResponse('Le montant de paiement est incorrect');
            }

            Help::historique(
                'agrement', /* Le service*/
                'Demande d\'agrément effectuée',/* Le titre*/
                'demande-agrement-init',/* L'action*/
                "Votre demande d'agrément a été envoyée avec succès, et est en attente de validation", /* L'action*/
                $demande->promoteur,/* Le promoteur  concerné */
                $demande/* Le model concerné */
            );

            $paiement = $this->createPayment($paymentData, $demande, $demande->promoteur);

            $data["state"] = "init";
            $demande->update($data);
            $demande->setAttribute('paiement', $paiement);
            return $this->successResponse($demande, "Votre demande d'agrément a été envoyée avec succès, et est en attente de validation");
        } catch (\Throwable $th) {

            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la demande");
        }
    }
    private function createPayment(array $data, Model $model, User $promoteur)
    {
        $data = array_merge([
            'payment_for' => 'demande-agrement',
            'date_payment' => now(),
            'promoteur_id' => $promoteur->id,
            'npi' => $promoteur->npi
        ], $data);
        $paiement = Help::paid($data, $model);

        Help::historique(
            "agrement",
            'Paiment effectué',
            'paiement-approved',
            $message = "Votre paiement de {$paiement->montant} FCFA pour demande d'agrément a été effectué avec succès",
            $promoteur,
            $paiement
        );

        $paiement->setAttribute('message', $message);
        return $paiement;
    }

    private function storeFiches(Request $request, DemandeAgrement $demande)
    {
        $fiches = [
            'nat_promoteur',
            'casier_promoteur',
            'ref_promoteur',
            'reg_commerce',
            'attest_fiscale',
            'attest_reg_organismes',
            'descriptive_locaux',
            'copie_statut',
            "carte_grise",
            "assurance_visite",
            "photo_vehicules"
        ];

        $data = [
            'demande_agrement_id' => $demande->id
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
                } else {
                    $path = $fiche->store("fiches", "public");
                }
                $data[$fiche_name] = $path;
            }
        }

        return DemandeAgrementFile::updateOrCreate([
            'demande_agrement_id' => $demande->id
        ], $data);
    }


    public function rejets($demandeRejet)
    {
        try {
            if (!auth()->check()) {
                return $this->errorResponse("Vous n'avez pas les autorisations nécessaire pour accéder à cette page", statuscode: 403);
            }
            $demandeRejet =  DemandeAgrementRejet::find($demandeRejet);

            if (!$demandeRejet) {
                return $this->errorResponse("Ce rejet de demande d'agrément est introuvable", statuscode: 404);
            }
            /**
             * @var \App\Models\DemandeAgrement $demande
             */
            $demande = $demandeRejet->demandeAgrement;

            $demande->load('fiche');

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

        if (!auth()->check()) {
            return $this->errorResponse("Vous n'avez pas les autorisations nécessaire pour accéder à cette page", statuscode: 403);
        }
        $user = auth()->user();
        $v = Validator::make($request->all(), [
            'demande_rejet_id' => 'required|integer|exists:demande_agrement_rejets,id',
            'departement_id' => 'required|numeric',
            'commune_id' => 'required|numeric',
            'moniteurs' => 'required',
            'telephone_pro' => 'required|string',
            'email_pro' => 'required|email',
            "vehicules" => "required|array"
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors(), statuscode: 422);
        }
        DB::beginTransaction();
        try {

            if (!$this->ifuWasVerified($user->npi, $request->get('ifu'))) {
                return $this->errorResponse("Vous devez vérifier l'IFU avant de continuer");
            }
            if ($demandeRejet != $request->demande_rejet_id) {
                return $this->errorResponse("Ce rejet de demande d'agrément est introuvable", statuscode: 404);
            }

            $demandeRejet =  DemandeAgrementRejet::find($demandeRejet);
            $data = $v->validate();

            unset($data['demande_rejet_id']);
            /**
             * @var \App\Models\DemandeAgrement $demande
             */
            $demande = $demandeRejet->demandeAgrement;

            $v = Validator::make($request->all(), [
                'auto_ecole' =>  ['required', 'min:2', Rule::unique('demande_agrements')->ignore($demande->id)],
                'ifu' => ['required'],
            ]);

            if ($v->fails()) {
                return $this->errorResponse("La validation a échoué", $v->errors(), statuscode: 422);
            }
            $data['state'] = "pending";
            if ($demande->promoteur_id != auth()->id()) {
                return $this->errorResponse("Vous n'avez pas les autorisations pour modifier cette demande", statuscode: 403);
            }
            $immatriculations = $request->vehicules;
            $data['vehicules'] = json_encode(array_map(
                function ($item) {
                    return ['immatriculation' => $item];
                },
                $immatriculations
            ));
            $demande->update($data);

            $demande->load('promoteur');

            $demandeRejet->update([
                'state' => 'pending',
                'date_correction' => now()
            ]);

            $this->storeFiches($request, $demande);

            Help::historique(
                'agrement',
                'Correction de rejet d\'agrément envoyée',
                'demande-agrement-pending',
                $message = "La correction de votre demande  d'agrément a été envoyée avec succès, et est en attente de validation",
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

    private function ifuWasVerified($npi, $ifu)
    {
        // return PromoteurIfu::where([
        //     'npi' => $npi,
        //     'ifu' => $ifu,
        //     'verified' => true,
        // ])->exists();

        return true;
    }
}
