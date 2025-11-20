<?php

namespace App\Http\Controllers;

use Exception;
use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierSession;
use App\Models\CandidatPayment;
use App\Models\DossierCandidat;
use App\Models\Service;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CandidatPaymentController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/candidat-payments",
     *      operationId="getCandidatPayments",
     *      tags={"CandidatPayments"},
     *      summary="Obtient la liste des paiements des candidats",
     *      description="Obtient la liste des paiements des candidats enregistrés dans la base de données",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des paiements des candidats",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID des paiements des candidats",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="candidat_id",
     *                      description="ID du candidat",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto-école",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="agregateur",
     *                      description="Nom de l'agrégateur",
     *                      type="string",
     *                      example="Nom de l'agrégateur"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Description du paiement",
     *                      type="string",
     *                      example="Description du paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="transaction_id",
     *                      description="ID de transaction",
     *                      type="string",
     *                      example="123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="reference",
     *                      description="Référence du paiement",
     *                      type="string",
     *                      example="REF789456"
     *                  ),
     *                  @OA\Property(
     *                      property="mode",
     *                      description="Mode de paiement",
     *                      type="string",
     *                      example="Mode de paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="operation",
     *                      description="Opération de paiement",
     *                      type="string",
     *                      example="Opération de paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="transaction_key",
     *                      description="Clé de transaction",
     *                      type="string",
     *                      example="TransKey123"
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Le montant payé",
     *                      type="string",
     *                      example="50.00"
     *                  ),
     *                  @OA\Property(
     *                      property="phone_payment",
     *                      description="Numéro de téléphone utilisé pour le paiement",
     *                      type="string",
     *                      example="+1234567890"
     *                  ),
     *                  @OA\Property(
     *                      property="ref_operateur",
     *                      description="La référence de l'opérateur",
     *                      type="string",
     *                      example="OP123456"
     *                  ),
     *                  @OA\Property(
     *                      property="numero_recu",
     *                      description="Le numéro du reçu",
     *                      type="string",
     *                      example="REC789456"
     *                  ),
     *                  @OA\Property(
     *                      property="moyen_payment",
     *                      description="Le moyen de paiement (momo ou portefeuille)",
     *                      type="string",
     *                      example="momo"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut du paiement (pending, approved, declined ou canceled)",
     *                      type="string",
     *                      example="approved"
     *                  ),
     *                  @OA\Property(
     *                      property="num_transaction",
     *                      description="Numéro de transaction délivré par l'agrégateur",
     *                      type="string",
     *                      example="123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="date_payment",
     *                      description="Date de paiement",
     *                      type="string",
     *                      format="date",
     *                      example="2023-07-19"
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                      example=1
     *                  ),
     *              )
     *          )
     *      )
     * )
     */
    public function index()
    {
        try {
            $candidat_payments = CandidatPayment::orderByDesc('created_at')->paginate(10);
            return $this->successResponse($candidat_payments);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des paiements');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *      path="/api/anatt-candidat/candidat-payments",
     *      operationId="createCandidatPayment",
     *      tags={"CandidatPayments"},
     *      summary="Enrégistrer un nouveau paiement du candidat",
     *      description="Crée un nouveau paiement du candidat enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto-école",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="agregateur",
     *                      description="Nom de l'agrégateur",
     *                      type="string",
     *                      example="Nom de l'agrégateur"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Description du paiement",
     *                      type="string",
     *                      example="Description du paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="transaction_id",
     *                      description="ID de transaction",
     *                      type="string",
     *                      example="123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="reference",
     *                      description="Référence du paiement",
     *                      type="string",
     *                      example="REF789456"
     *                  ),
     *                  @OA\Property(
     *                      property="mode",
     *                      description="Mode de paiement",
     *                      type="string",
     *                      example="Mode de paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="operation",
     *                      description="Opération de paiement",
     *                      type="string",
     *                      example="Opération de paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="transaction_key",
     *                      description="Clé de transaction",
     *                      type="string",
     *                      example="TransKey123"
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Le montant payé",
     *                      type="string",
     *                      example="50.00"
     *                  ),
     *                  @OA\Property(
     *                      property="phone_payment",
     *                      description="Numéro de téléphone utilisé pour le paiement",
     *                      type="string",
     *                      example="+1234567890"
     *                  ),
     *                  @OA\Property(
     *                      property="ref_operateur",
     *                      description="La référence de l'opérateur",
     *                      type="string",
     *                      example="OP123456"
     *                  ),
     *                  @OA\Property(
     *                      property="numero_recu",
     *                      description="Le numéro du reçu",
     *                      type="string",
     *                      example="REC789456"
     *                  ),
     *                  @OA\Property(
     *                      property="moyen_payment",
     *                      description="Le moyen de paiement (momo ou portefeuille)",
     *                      type="string",
     *                      example="momo"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut du paiement (pending, approved, declined ou canceled)",
     *                      type="string",
     *                      example="approved"
     *                  ),
     *                  @OA\Property(
     *                      property="num_transaction",
     *                      description="Numéro de transaction délivré par l'agrégateur",
     *                      type="string",
     *                      example="123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="date_payment",
     *                      description="Date de paiement",
     *                      type="string",
     *                      format="date",
     *                      example="2023-07-19"
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                      example=1
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau paiement du candidat créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouveau paiement du candidat créé",
     *                  type="integer",
     *              ),
     *      )
     * )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'auto_ecole_id' => 'nullable|integer',
                'agregateur' => 'required|string',
                'description' => 'required|string',
                'transaction_id' => 'required|integer',
                'reference' => 'required|string',
                'mode' => 'required|string',
                'operation' => 'required|string',
                'transaction_key' => 'required|string',
                'montant' => 'required|numeric|min:0',
                'phone_payment' => 'required|string|min:8|max:25',
                'ref_operateur' => 'nullable|string',
                'numero_recu' => 'nullable|string|max:100|unique:auto_ecole_payments,numero_recu,NULL,id,agregateur_id,' . $request->agregateur,
                'moyen_payment' => 'required|in:momo,portefeuille',
                'status' => 'required|in:pending,approved,declined,canceled',
                'num_transaction' => 'nullable|string|max:100|unique:auto_ecole_payments,num_transaction,NULL,id,agregateur_id,' . $request->agregateur,
                'date_payment' => 'nullable|date',
                'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
                'session_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue lors de la création du paiement', $validator->errors()->toArray());
            }
            // Récupérer l'ID de l'utilisateur connecté (candidat_id)
            $candidatId = auth()->id();
            $dateSoumission = now();
            // Ajouter l'ID de l'utilisateur connecté dans la requête
            $request->merge(['candidat_id' => $candidatId]);
            $montant = $request->input('montant');
            $session = $request->input('session_id');
            $dossier_candidat_id = $request->input('dossier_candidat_id');


            $dossier = DossierCandidat::findOrFail($dossier_candidat_id);
            $d_session = DossierSession::where('dossier_candidat_id', $dossier->id)->latest()->first();
            $transactionId = $request->input('transaction_id');
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
                return $this->errorResponse('Fedapay indique que le paiement a échoué.');
            }
            $montantPayment = $d_session->montant_paiement;
            if ($transaction->amount != $montantPayment) {
                // logger()->error(json_encode($paymentData));
                return $this->errorResponse('Le montant de paiement est incorrect');
            }
            if ($d_session) {
                // Mettre à jour le champ "state" du dossier avec la valeur de la variable $state
                $d_session->bouton_paiement = -1;
                $d_session->examen_id = $session;
                $d_session->state = "payment";
                $d_session->date_payment = now();
                $d_session->save();
                $dossier_session_id =  $d_session->id;
            }

            // Récupérer la liste des permis depuis l'endpoint
            $path = "categorie-permis";
            $response = Api::base('GET', $path);

            if ($response->successful()) {
                $permis = $response->json()['data'];

                $categoriePermisId = $dossier->categorie_permis_id;
                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;
            }

            $suivi = ParcoursSuivi::where('dossier_candidat_id', $dossier_candidat_id)
                ->where('slug', 'monitoring')
                ->orderByDesc('created_at')
                ->first();

            if (!$suivi) {
                return $this->errorResponse('Parcours suivi non trouvé');
            }
            $suivi->bouton = '{"bouton":"Paiement","status":"-1"}';
            $suivi->save();

            $dossierId = $d_session->id;
            $encryptedDossierId = Crypt::encrypt($dossierId);
            $url = route('generate-facture', ['encryptedDossierId' => $encryptedDossierId]);

            // Enregistrement dans le modèle ParcoursSuivi
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $dossier->npi;
            $parcoursSuivi->slug = 'inscription';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
            $parcoursSuivi->dossier_session_id = $dossier_session_id;
            $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;
            $parcoursSuivi->message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'inscription à la catégorie de permis de conduire " . $nomPermis;
            $parcoursSuivi->date_action = $dateSoumission;
            $parcoursSuivi->url = $url;
            $parcoursSuivi->save();

            $description = $request->input('description') . " " . $suivi->service . " categorie " . $nomPermis;
            $request->merge(['description' => $description]);
            $request->merge(['dossier_session_id' => $dossier_session_id]);

            $candidat_payment = CandidatPayment::create($request->all());
            $candidat_payment->examen_id = $session;
            $candidat_payment->save();


            // $candidat_payment = CandidatPayment::create($request->all());
            $candidat_payment['url'] = $url;
            $this->openMonitoring($dossier_session_id);
            return $this->successResponse($candidat_payment, 'Paiement ajouté avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la création du paiement');
        }
    }

    private function openMonitoring($dossier_session_id)
    {
        $response = Api::base("POST", "dossier-sessions/suivi-candidat/state", [
            'state' => "pending",
            'dossier_session_id' => $dossier_session_id
        ]);

        $data  = Api::data($response);
    }

    public function createTransaction(Request $request)
    {
        try {
            $request->validate([
                "session_id" => "required|exists:examens,id",
            ]);
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $npi = $user->npi;
            $dossierSession  = DossierSession::where('npi', $npi)->latest()->first();

            $userAnnexeId = $dossierSession->annexe_id;
            if($userAnnexeId != '8'){
                // Vérifier si le nombre d'inscriptions non closes pour cette session d'examen dépasse 20
                $session_id = $request->input('session_id');
                $nombreInscriptions = DossierSession::where('examen_id', $session_id)
                                                    ->where('closed', false)
                                                    ->count();
                if ($nombreInscriptions == 30) {
                    return $this->errorResponse('Cette session est complète. Veuillez sélectionner une autre session d\'examen.', null, null, 422);
                }
            }

            // Montant de paiement initial
            $amount = $dossierSession->montant_paiement;

            // Définir les montants à ajouter selon l'ID de la catégorie de permis
            $montants_permis = config('anatt.amounts.frais-permis');

            // Vérifier l'ID de la catégorie de permis
            $categorie_permis_id = $dossierSession->categorie_permis_id; // Assurez-vous que cette colonne existe

            // Montant additionnel basé sur l'ID de la catégorie de permis
            $montant_additionnel = isset($montants_permis[$categorie_permis_id]) ? $montants_permis[$categorie_permis_id] : 0;

            // Calcul du montant total
            $total_amount = $amount + 2000 + $montant_additionnel;

            // Démarrer le processus de paiement avec le montant total
            $transaction = $this->startPaymentProcess([
                "totalAmount" => $total_amount,
                "amount" => $amount,
                "tresorAmount" => $montant_additionnel,
            ]);
            $transactionId = data_get($transaction, "id");
            $subscription = UserTransaction::create([
                'uuid' => Str::uuid(),
                'service' => 'ds-code-conduite',
                'service_id' => $dossierSession->id,
                'npi' => $npi,
                'status' => 'init',
                "amount" => $total_amount,
                "transaction_id" => $transactionId,
                "expired_at" => now()
            ]);
            $session = $request->input('session_id');
            $dossierSession->examen_id = $session;
            $dossierSession->save();
            $subscription->save();

            $tokenUrl = $transaction->generateToken();
            return $this->successResponse([
                "uuid" => $subscription->uuid,
                "fedapay" => $tokenUrl,
                "transactionId" => $transactionId
            ], 'Paiement créée avec succès');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Erreur lors de la création de le demande', $th->getMessage(), '', 500);
        }
    }
    private function startPaymentProcess(array $data)
    {

        $fedaPayEnv = env('FEDAPAY_ENV', 'sandbox');
        if ($fedaPayEnv === 'live') {
            \FedaPay\FedaPay::setEnvironment('live');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_LIVE_PRIVATE_KEY'));
        } else {
            \FedaPay\FedaPay::setEnvironment('sandbox');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_SANDBOX_PRIVATE_KEY'));
        }

        $user = auth()->user();
        return \FedaPay\Transaction::create(array(
            "description" => "Souscription à " . env('APP_ENV'),
            "amount" => data_get($data, "totalAmount"),
            "currency" => ["iso" => "XOF"],
            "callback_url" => route("payments.codecprocced"),
            "sub_accounts_commissions" => [
                [
                    "reference" => env('SUB_ACCOUNT_ANATT_PC'), // ANaTT-PC
                    "amount" => data_get($data, "amount")
                ],
                [
                    "reference" => env('SUB_ACCOUNT_ANATT_TRESOR'), // ANaTT-TRESOR
                    "amount" => data_get($data, "tresorAmount")
                ],
                [
                    "reference" => env('SUB_ACCOUNT_ANATT_DGI'), // ANaTT-DGI
                    "amount" => 2000
                ],
            ],

        ));
    }


    public function procced(Request $request)
    {
        $request->validate([
            "id" => "required",
        ]);
        $fedaPayEnv = env('FEDAPAY_ENV', 'sandbox');
        if ($fedaPayEnv === 'live') {
            \FedaPay\FedaPay::setEnvironment('live');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_LIVE_PRIVATE_KEY'));
        } else {
            \FedaPay\FedaPay::setEnvironment('sandbox');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_SANDBOX_PRIVATE_KEY'));
        }

        $ID = $request->get('id');
        $transaction = \FedaPay\Transaction::retrieve($ID);
        $subscription = UserTransaction::where(["transaction_id" => $ID])->first();

        if (!$subscription) {
            return null;
        }
        if ($subscription->status == 'approved') {
            return null;
        }
        if ($transaction->status == "approved") {
            $paid = true;

            $subscription->status = "approved";
            $subscription->expired_at = now()->addMonths(1);
            $subscription->save();

            //la continuité
            $Id = $subscription->service_id;
            $npi = $subscription->npi;
            $amount = $subscription->amount;
            $transactionid = $subscription->transaction_id;
            $demande  = DossierSession::where('npi', $npi)->latest()->first();
            $user  = User::where('npi', $npi)->first();

            // Récupérer l'ID de l'utilisateur connecté (candidat_id)
            $candidatId = $user->id;
            $dateSoumission = now();
            // Ajouter l'ID de l'utilisateur connecté dans la requête
            $request->merge(['candidat_id' => $candidatId]);
            $montant = $amount;
            $session = $demande->examen_id;
            $dossier_candidat_id = $demande->dossier_candidat_id;


            $dossier = DossierCandidat::findOrFail($dossier_candidat_id);
            $d_session = DossierSession::where('dossier_candidat_id', $dossier->id)->latest()->first();
            $transactionId = $ID;
            if ($d_session) {
                // Mettre à jour le champ "state" du dossier avec la valeur de la variable $state
                $d_session->bouton_paiement = -1;
                $d_session->examen_id = $session;
                $d_session->state = "payment";
                $d_session->date_payment = now();
                $d_session->save();
                $dossier_session_id =  $d_session->id;
            }

            // Récupérer la liste des permis depuis l'endpoint
            $path = "categorie-permis";
            $response = Api::base('GET', $path);

            if ($response->successful()) {
                $permis = $response->json()['data'];

                $categoriePermisId = $dossier->categorie_permis_id;
                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;
            }

            $suivi = ParcoursSuivi::where('dossier_candidat_id', $dossier_candidat_id)
                ->where('slug', 'monitoring')
                ->orderByDesc('created_at')
                ->first();

            if (!$suivi) {
                return $this->errorResponse('Parcours suivi non trouvé');
            }
            $suivi->bouton = '{"bouton":"Paiement","status":"-1"}';
            $suivi->save();

            $dossierId = $d_session->id;
            $encryptedDossierId = Crypt::encrypt($dossierId);
            $url = route('generate-facture', ['encryptedDossierId' => $encryptedDossierId]);

            // Enregistrement dans le modèle ParcoursSuivi
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $dossier->npi;
            $parcoursSuivi->slug = 'inscription';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
            $parcoursSuivi->dossier_session_id = $dossier_session_id;
            $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;
            $parcoursSuivi->message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'inscription à la catégorie de permis de conduire " . $nomPermis;
            $parcoursSuivi->date_action = $dateSoumission;
            $parcoursSuivi->url = $url;
            $parcoursSuivi->save();

            $description = "Paiement à l'ANaTT du Service" . " " . $suivi->service . " categorie " . $nomPermis;
            $request->merge(['description' => $description]);
            $request->merge(['dossier_session_id' => $dossier_session_id]);


            // Préparation des données de paiement
            $paymentData = [
                'candidat_id' => $candidatId,
                'auto_ecole_id' => $demande->auto_ecole_id,
                'agregateur' => 'fedaPay',
                'description' => $description,
                'transaction_id' => $transaction->id,
                'reference' => data_get($transaction, 'mode'),
                'mode' => data_get($transaction, 'mode'),
                'operation' => 'payment',
                'transaction_key' => data_get($transaction, 'mode'),
                'montant' => $montant,
                'ref_operateur' => data_get($transaction, 'mode'),
                'numero_recu' => data_get($transaction, 'mode'),
                'moyen_payment' => 'momo',
                'status' => 'approved',
                'date_payment' => now(),
                'dossier_candidat_id' => $dossier_candidat_id,
                'dossier_session_id' => $dossier_session_id,
                'examen_id' => $demande->examen_id,
            ];

            // Création de l'enregistrement de paiement
            $candidat_payment = CandidatPayment::create($paymentData);

            $candidat_payment['url'] = $url;
            $this->openMonitoring($dossier_session_id);
            return null;
        }
    }

    public function checkTransactionUuid($uuid)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $npi = $user->npi;
            $transaction = UserTransaction::where('uuid', $uuid)->first();

            if (!$transaction) {
                return $this->errorResponse('Cette transaction n\'existe pas', null, null, 422);
            }

            $transacNpi = $transaction->npi;
            if ($npi != $transacNpi) {
                return $this->errorResponse('Vous n\'avez pas le droit de consulter cette transaction', null, null, 422);
            }
            $amount = $transaction->amount;
            $candidatEndpoint = env('CANDIDAT');
            $encryptednpi = Crypt::encrypt($npi);
            $urlWithCode = $candidatEndpoint . 'generate-facture/' . $encryptednpi;
            $url = route('generate-facture', ['encryptednpi' => $encryptednpi]);
            // Collecte des données pour la réponse
            $data = [
                'uuid' => $transaction->uuid,
                'service' => $transaction->service,
                'npi' => $transaction->npi,
                'status' => $transaction->status,
                'amount' => $transaction->amount,
                'url' => $url,
                'date_payment' => $transaction->created_at,
            ];
            $message = "Paiement de " . $amount . "F CFA éffectué avec succès. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            return $this->successResponse($data, $message);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération. ' . $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/candidat-payments/{id}",
     *      operationId="getCandidatPaymentById",
     *      tags={"CandidatPayments"},
     *      summary="Affiche les détails d'un paiement du candidat",
     *      description="Affiche les détails d'un paiement du candidat enregistré dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du paiement du candidat à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Détails du paiement du candidat récupéré",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du paiement du candidat",
     *                  type="integer",
     *                  example=1
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Paiement du candidat non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $candidat_payment = CandidatPayment::findOrFail($id);
            return $this->successResponse($candidat_payment);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération du paiement');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *      path="/api/anatt-candidat/candidat-payments/{id}",
     *      operationId="updateCandidatPayment",
     *      tags={"CandidatPayments"},
     *      summary="Mettre à jour un paiement du candidat",
     *      description="Met à jour un paiement du candidat enregistré dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du paiement du candidat",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="candidat_id",
     *                      description="ID du candidats",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto école",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="agregateur_id",
     *                      description="Id de l'agregateur",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Le montant payé",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="moyen_payment",
     *                      description="le moyent du paiement(momo ou payment)",
     *                      type="boolean",
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status du paiement",
     *                      type="boolean",
     *                  ),
     *                  @OA\Property(
     *                      property="num_transaction",
     *                      description="Numéro de transaction délivré par l'agregateur",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="Id du dossier du candidat",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="phone_payment",
     *                      description="Numéro de téléphone utilsé pour le paiement",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="ref_operateur",
     *                      description="La référence de l'opérateur",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="numero_recu",
     *                      description="Le numéro du reçu",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="date_payment",
     *                      description="Date de paiement",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="paiement du candidat mis à jour",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du paiement du candidat mis à jour",
     *                  type="integer",
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Paiement du candidat non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'candidat_id' => 'required|integer',
                'auto_ecole_id' => 'required|integer',
                'agregateur_id' => 'required|integer',
                'montant' => 'sometimes|numeric|min:0',
                'moyen_payment' => 'required|in:momo,portefeuille',
                'status' => 'required|in:pending,success,failed',
                'num_transaction' => 'sometimes|string|max:100|unique:auto_ecole_payments,num_transaction,' . $request->id . ',id,agregateur_id,' . $request->agregateur_id,
                'dossier_candidat_id' => 'required|integer',
                'phone_payment' => 'sometimes|min:8|max:13',
                'numero_recu' => 'sometimes|unique:auto_ecole_payments,numero_recu,' . $request->id . ',id,agregateur_id,' . $request->agregateur_id,
                'ref_operateur' => 'sometimes',
                'date_payment' => 'sometimes|date',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue lors de la mise à jour du paiement', $validator->errors()->toArray());
            }

            $candidat_payment = CandidatPayment::findOrFail($id);
            $candidat_payment->update($request->all());
            return $this->successResponse($candidat_payment, 'Paiement mis à jour avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du paiement');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Delete(
     *      path="/api/anatt-candidat/candidat-payments/{id}",
     *      operationId="deleteCandidatPayment",
     *      tags={"CandidatPayments"},
     *      summary="Supprime un paiement du candidat existant",
     *      description="Supprime un paiement du candidat spécifié de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du paiement du candidat à supprimer",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Paiement du candidat supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Paiement du candidat non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $candidat_payment = CandidatPayment::findOrFail($id);
            $candidat_payment->delete();
            return $this->successResponse('Paiement supprimé avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la suppression du paiement');
        }
    }
}
