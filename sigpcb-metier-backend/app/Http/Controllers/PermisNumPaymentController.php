<?php

namespace App\Http\Controllers;

use App\Mail\PermisNumMail;
use App\Services\Api;

use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierCandidat;
use App\Models\PermisNumPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class PermisNumPaymentController extends ApiController
{

    public function checkPermis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categorie_permis_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidatorErrors($validator);
        }
        $user = Auth::user();
        if (!$user) {
            return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
        }
        $npi = $user->npi;
        $categorie_permis_id = $request->input('categorie_permis_id');
        $response = Api::base("POST", "demande-permis/check-permis", [
            'categorie_permis_id' => $categorie_permis_id,
            'npi' => $npi
        ]);

        if (!$response->successful()) {
            return $this->successResponse(null, "Aucun permis trouvé", 404);
        }
        $data  = Api::data($response);
        if ($data) {
            return $this->successResponse($data);
        }
        return $this->errorResponse('Aucun résultat trouvé', 404);
    }

    public function getUserPermis()
    {
        $user = Auth::user();
        if (!$user) {
            return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
        }
        $npi = $user->npi;
        $response = Api::base("POST", "candidat/permis", [
            'npi' => $npi
        ]);
        $data  = Api::data($response);
        return $this->successResponse($data);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'code_permis' => 'required',
                'categorie_permis_id' => 'required',
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
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue lors de la création du paiement', $validator->errors()->toArray());
            }
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $npi = $user->npi;
            $userId = $user->id;
            // Récupérer l'ID de l'utilisateur connecté (candidat_id)
            $candidatId = auth()->id();
            $dateSoumission = now();
            // Ajouter l'ID de l'utilisateur connecté dans la requête
            $request->merge(['candidat_id' => $candidatId]);
            $montant = $request->input('montant');
            $categorie_permis_id = $request->input('categorie_permis_id');
            $email = $request->input('email');
            $code_permis = $request->input('code_permis');
            $encryptedPermis = Crypt::encrypt($code_permis);
            $urlPermis = route('generate-permis', ['permis' => $encryptedPermis]);
            // Récupérer la liste des permis depuis l'endpoint
            $path = "categorie-permis";
            $response = Api::base('GET', $path);
            $dossiers = DossierCandidat::where('candidat_id', $userId)
            ->where('categorie_permis_id', $categorie_permis_id)
            ->orderByDesc('id')
            ->get();

            if ($dossiers->isEmpty()) {
                $dossier_candidat_id = null;
            } else {
                $dossier_candidat_id = $dossiers->first()->id;
            }
            if ($response->successful()) {
                $permis = $response->json()['data'];

                $categoriePermisId = $categorie_permis_id;
                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;
            }

            $requestData = $request->all();

            # Ajout du NPI
            $requestData['npi'] = $npi;
            $requestData['dossier_candidat_id'] = $dossier_candidat_id;
            unset($requestData['email']);
            unset($requestData['permis_id']);
            $candidat_payment = PermisNumPayment::create($requestData);

            // Enregistrement dans le modèle ParcoursSuivi
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $npi;
            $parcoursSuivi->slug = 'permis-numerique';
            $parcoursSuivi->service = 'Permis Numérique';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
            $parcoursSuivi->message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'obtention du permis numérique de la catégorie de permis "  . $nomPermis;
            $parcoursSuivi->date_action = $dateSoumission;
            $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
            $parcoursSuivi->permis_num_payment_id = $candidat_payment->id;
            $parcoursSuivi->url = $urlPermis;
            $parcoursSuivi->save();

            $encryptednpi = Crypt::encrypt($npi);
            $url = route('generate-numpermis-facture', ['encryptednpi' => $encryptednpi]);

            Mail::to($email)->send(new PermisNumMail($encryptedPermis));

            $candidat_payment['url'] = $url;
            $this->insertDemande($email, $categorie_permis_id, $npi);
            $candidatEndpoint = env('CANDIDAT');
            $urlWithCode = $candidatEndpoint . 'generate-permis/' . $encryptedPermis;
            $message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'obtention du permis numérique de la catégorie de permis " . $nomPermis . ". Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            return $this->successResponse($candidat_payment, $message);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la création du paiement');
        }
    }

    private function insertDemande($email, $categorie_permis_id, $npi)
    {
        $response = Api::base("POST", "demande-permis", [
            'email' => $email,
            'categorie_permis_id' => $categorie_permis_id,
            'npi' => $npi,
        ]);

        $data  = Api::data($response);
    }
}
