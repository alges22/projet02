<?php

namespace App\Http\Controllers;

use App\Mail\AttestationMail;
use App\Mail\PermisNumMail;
use App\Models\Base\Permis;
use App\Models\DossierCandidat;
use App\Models\EserviceParcourSuivi;
use App\Models\EservicePayment;
use App\Models\ParcoursSuivi;
use App\Models\Service;
use App\Models\SuccesAttestation;
use App\Models\User;
use App\Models\UserTransaction;
use App\Services\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class SuccesAttestationController extends ApiController
{

    public function verifyPermit($code)
    {
        $permis = Permis::where('code_permis', $code)->first();

        if ($permis) {
            return $this->successResponse('Document valide');
        }

        return $this->errorResponse('Document non valide.');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'permis_id' => 'required|exists:permis,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue lors de la création du paiement', $validator->errors()->toArray());
            }
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $permis = Permis::find($request->get('permis_id'));
            if (!$permis) {
                return $this->errorResponse('Le numéro de permis n\'existe pas.', null, null, 422);
            }
            // Récupérer l'ID de l'utilisateur connecté (candidat_id)
            $npi = $user->npi;
            $candidatId = auth()->id();
            $dateSoumission = now();
            $userId = $user->id;
            $categorie_permis_id = $permis->categorie_permis_id;
            $dossier_session_id = $permis->dossier_session_id;
            $code_permis = $permis->code_permis;
            $email = $request->input('email');
            $encryptedPermis = Crypt::encrypt($request->get('permis_id'));
            $urlPermis = route('generate-attestation', ['permis' => $encryptedPermis]);
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

            // Enregistrement dans le modèle ParcoursSuivi
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $npi;
            $parcoursSuivi->slug = 'attestation';
            $parcoursSuivi->service = 'Attestation';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
            $parcoursSuivi->message = "Demande d'attestation de permis effectuée avec succès";
            $parcoursSuivi->date_action = $dateSoumission;
            $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
            $parcoursSuivi->url = $urlPermis;
            $parcoursSuivi->save();

            $attestation = SuccesAttestation::create([
                'npi' => $npi,
                'email' => $email,
                'candidat_id' => $candidatId,
                'categorie_permis_id' => $categoriePermisId,
                'dossier_candidat_id' => $dossier_candidat_id,
                'status' => 'approved',
            ]);

            Mail::to($email)->send(new AttestationMail($encryptedPermis));
            $candidatEndpoint = env('CANDIDAT');
            $urlWithCode = $candidatEndpoint . 'generate-attestation/' . $encryptedPermis;
            $message = "Demande d'attestation de permis effectuée avec succès";
            return $this->successResponse($attestation, $message);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue');
        }
    }
}
