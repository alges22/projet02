<?php

namespace App\Http\Controllers\Programmation;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\Candidat as User;
use App\Models\Candidat\DossierSession;
use App\Models\Candidat\ParcoursSuivi;
use App\Services\Api;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\PayloadFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class ConvocationController extends ApiController
{

    public function sendConvocations(Request $request)
    {
        // Validation des paramètres de la requête
        $v = Validator::make($request->all(), [
            'annexe_id' => 'required|integer',
            "examen_id" => 'required|integer',
        ]);

        if ($v->fails()) {
            // Retourne un message d'erreur si la validation échoue
            return $this->errorResponse("La validation a échoué", $v->errors());
        }

        // Appel à la fonction storeConvocationCode pour gérer la création de la convocation
        $response = $this->storeConvocationCode($request);

        // Vérifie si la réponse contient une clé 'error'
        if (isset($response['error'])) {
            // Retourne un message d'erreur si l'opération a échoué
            return $this->errorResponse($response['message'], $response['errors'], $response['data'], $response['statuscode']);
        }
        // Retourne une réponse en cas de succès
        return $this->successResponse($response['data'], $response['message'], $response['statuscode']);
    }

    private function storeConvocationCode(Request $request)
    {
        // Validation des paramètres de la requête
        $v = Validator::make($request->all(), [
            'examen_id' => 'required|integer',
            'annexe_id' => 'required|integer',
            'agent_id' => 'required|integer',
        ]);

        if ($v->fails()) {
            return [
                'error' => true,
                'message' => 'La validation a échoué',
                'errors' => $v->errors(),
                'data' => null,
                'statuscode' => 422
            ];
        }

        try {
            $examenId = $request->input('examen_id');
            $annexeId = $request->input('annexe_id');
            $agentId = $request->input('agent_id');

            // Obtenez toutes les sessions de dossiers correspondantes
            $dossierSessions = DossierSession::where('examen_id', $examenId)
                ->where('annexe_id', $annexeId)
                ->where('closed', false)
                ->where('type_examen', 'code-conduite')
                ->where('state', 'validate')
                ->get();

            if ($dossierSessions->isEmpty()) {
                return [
                    'error' => true,
                    'message' => 'Aucun candidat trouvé',
                    'errors' => null,
                    'data' => null,
                    'statuscode' => 404
                ];
            }

            $parcoursSuivi = null;

            // Boucle sur chaque dossier de session trouvé
            foreach ($dossierSessions as $dossierSession) {
                $npi = $dossierSession->npi;
                $dossierId = $dossierSession->id;
                $categorie_permis_id = $dossierSession->categorie_permis_id;
                $dossier_candidat_id = $dossierSession->dossier_candidat_id;
                $date_soumission = now();
                $message = "Vous avez été programmé. Veuillez cliquer sur le bouton pour télécharger votre convocation.";

                // Combiner le NPI et le dossierId pour créer une chaîne et encoder en base64
                $encodedNpi= base64_encode($npi);
                $encodedDossier= base64_encode($dossierId);
                $encodedData = $encodedNpi . '-' . $encodedDossier;

                // URL sécurisée contenant la chaîne encodée en base64
                $url = env('CANDIDAT_BACKEND') . $encodedData;

                $user = User::where('npi', $npi)->firstOrFail();
                $candidat_id = $user->id;

                // Vérifier si une entrée ParcoursSuivi existe déjà avec les mêmes critères
                $existingEntry = ParcoursSuivi::where('dossier_session_id', $dossierId)
                    ->where('categorie_permis_id', $categorie_permis_id)
                    ->where('slug', 'convocation-code')
                    ->first();

                if ($existingEntry) {
                    $existingEntry->url = $url;
                    $existingEntry->save();
                } else {
                    $parcoursSuivi = new ParcoursSuivi();
                    $parcoursSuivi->npi = $npi;
                    $parcoursSuivi->slug = "convocation-code";
                    $parcoursSuivi->service = 'Permis';
                    $parcoursSuivi->candidat_id = $candidat_id;
                    $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                    $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                    $parcoursSuivi->message = $message;
                    $parcoursSuivi->agent_id = $agentId;
                    $parcoursSuivi->bouton = json_encode(['bouton' => 'Convocation', 'status' => '1']);
                    $parcoursSuivi->dossier_session_id = $dossierId;
                    $parcoursSuivi->url = $url;
                    $parcoursSuivi->date_action = $date_soumission;
                    $parcoursSuivi->save();
                }
            }

            return [
                'error' => false,
                'message' => 'Traitement effectué avec succès.',
                'errors' => null,
                'data' => $parcoursSuivi,
                'statuscode' => 200
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            logger()->error($e);
            return [
                'error' => true,
                'message' => 'Dossier candidat non trouvé.',
                'errors' => null,
                'data' => null,
                'statuscode' => 422
            ];
        } catch (\Throwable $e) {
            logger()->error($e);
            return [
                'error' => true,
                'message' => 'Une erreur est survenue lors de l\'insertion',
                'errors' => null,
                'data' => null,
                'statuscode' => 500
            ];
        }
    }


    public function sendConduiteConvocations(Request $request)
    {
        $v = Validator::make($request->all(), [
            'annexe_id' => 'required|integer',
            "examen_id" => 'required|integer',
        ]);

        if ($v->fails()) {
            return $this->errorResponse("La validation a échoué", $v->errors());
        }

        // $response = Api::candidat('POST', "parcours-suivis/convocation-conduites", $request->all());
        $response = $this->storeConvocationConduite($request);
        // Vérifie si la réponse contient une clé 'error'
        if (isset($response['error'])) {
            // Retourne un message d'erreur si l'opération a échoué
            return $this->errorResponse($response['message'], $response['errors'], $response['data'], $response['statuscode']);
        }
        // Retourne une réponse en cas de succès
        return $this->successResponse($response['data'], $response['message'], $response['statuscode']);
    }


    private function storeConvocationConduite(Request $request)
    {
        $v = Validator::make($request->all(), [
            'examen_id' => 'required|integer',
            'annexe_id' => 'required|integer',
            'agent_id' => 'required|integer',
        ]);

        if ($v->fails()) {
            // Retourne un message d'erreur si la validation échoue
            return [
                'error' => true,
                'message' => 'La validation a échoué',
                'errors' => $v->errors(),
                'data' => null,
                'statuscode' => 422
            ];
        }
        try {
            $examenId = $request->input('examen_id');
            $annexeId = $request->input('annexe_id');
            $agentId = $request->input('agent_id');

            // Obtenez toutes les sessions de dossiers correspondantes
            $dossierSessions = DossierSession::where('examen_id', $examenId)
                ->where('annexe_id', $annexeId)
                ->where('resultat_code', 'success')
                ->get();

            if ($dossierSessions->isEmpty()) {
                // Retourne un message si aucun candidat n'a été trouvé
                return [
                    'error' => true,
                    'message' => 'Aucun candidat trouvé',
                    'errors' => null,
                    'data' => null,
                    'statuscode' => 404
                ];
            }
            $parcoursSuivi = null;
            foreach ($dossierSessions as $dossierSession) {
                $npi = $dossierSession->npi;
                $dossierId = $dossierSession->id;
                $categorie_permis_id = $dossierSession->categorie_permis_id;
                $dossier_candidat_id = $dossierSession->dossier_candidat_id;
                $date_soumission = now();
                $message = "Vous avez été programmé pour la conduite. Veuillez cliquer sur le bouton pour télécharger votre convocation.";
                $encodedNpi= base64_encode($npi);
                $encodedDossier= base64_encode($dossierId);
                $encodedData = $encodedNpi . '-' . $encodedDossier;

                //URL sécurisée contenant la chaîne encodée en base64
                $url = env('CANDIDAT_CONDUITE_BACKEND') . $encodedData;

                $user = User::where('npi', $npi)->firstOrFail();
                $candidat_id = $user->id;
                // Vérifier si une entrée ParcoursSuivi existe déjà avec les mêmes critères
                $existingEntry = ParcoursSuivi::where('dossier_session_id', $dossierId)
                    ->where('categorie_permis_id', $categorie_permis_id)
                    ->where('slug', 'convocation-conduite')
                    ->first();

                if ($existingEntry) {
                    // Mettre à jour l'entrée existante avec la nouvelle URL
                    $existingEntry->url = $url;
                    $existingEntry->save();
                } else {
                    // Créer une nouvelle entrée dans ParcoursSuivi
                    $parcoursSuivi = new ParcoursSuivi();
                    $parcoursSuivi->npi = $npi;
                    $parcoursSuivi->slug = "convocation-conduite";
                    $parcoursSuivi->service = 'Permis';
                    $parcoursSuivi->candidat_id = $candidat_id;
                    $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                    $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                    $parcoursSuivi->message = $message;
                    $parcoursSuivi->agent_id = $agentId;
                    $parcoursSuivi->bouton = json_encode(['bouton' => 'Convocation', 'status' => '1']);
                    $parcoursSuivi->dossier_session_id = $dossierId;
                    $parcoursSuivi->url = $url;
                    $parcoursSuivi->date_action = $date_soumission;
                    $parcoursSuivi->save();
                }
            }
            return [
                'error' => false,
                'message' => 'Convocation envoyée avec succès.',
                'errors' => null,
                'data' => $parcoursSuivi,
                'statuscode' => 200
            ];
            // return $this->successResponse($parcoursSuivi, 'Convocation envoyée avec succès.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            logger()->error($e);
            return [
                'error' => true,
                'message' => 'Dossier candidat non trouvé.',
                'errors' => null,
                'data' => null,
                'statuscode' => 422
            ];
            // return $this->errorResponse('Dossier candidat non trouvé.', null, null, 422);
        } catch (\Throwable $e) {
            logger()->error($e);
            return [
                'error' => true,
                'message' => 'Une erreur est survenue lors de l\'insertion',
                'errors' => null,
                'data' => null,
                'statuscode' => 500
            ];
        }
    }
}
