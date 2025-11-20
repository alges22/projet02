<?php

namespace App\Http\Controllers;

use App\Models\Jurie;
use App\Services\Api;
use App\Models\Examen;
use App\Models\AnnexeAnatt;
use App\Models\Examinateur;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Http\Controllers\ApiController;
use App\Models\Base\JuryCandidat;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Validator;
use App\Models\Candidat\ConvocationConduite;

class CodeExaminateurController extends ApiController
{

    private function getExaminateurId()
    {
        $userId = auth()->id();

        // Recherche de l'inspecteur associé à l'utilisateur connecté
        $examinateur = Examinateur::where('user_id', $userId)->first();

        if ($examinateur) {
            // Si un inspecteur est trouvé, renvoyer son ID
            $examinateur_id = $examinateur->id;
            return $examinateur_id;
        } else {
            // Si aucun inspecteur n'est trouvé, renvoyer un message d'erreur

            return $this->errorResponse("Vous devez être un examinateur pour continuer sur cette page.");
        }
    }

    public function getExaminateurJury($examen_id)
    {
        $examinateurId = $this->getExaminateurId();

        if (is_numeric($examinateurId)) {
            // Récupérez les jurys de cet examinateur à partir de la table Jurie
            $jurys = Jurie::where('examinateur_id', $examinateurId)
                ->where('examen_id', $examen_id)
                ->get();

            // Maintenant, $jurys contient tous les jurys associés à cet examinateur
            return $this->successResponse($jurys);
        } else {
            return $examinateurId;
        }
    }

    public function getDossierbyJury(Request $request)
    {
        $examinateurId = $this->getExaminateurId();
        // Obtenez l'examinateur_id à partir de la table Jurie en utilisant le jury_id de la requête
        $juryId = $request->input('jury_id');
        $examenId = $request->input('examen_id');
        $jury = Jurie::find($juryId);
        // Vérifiez si le jury existe
        if (!$jury) {
            return $this->errorResponse("Jury introuvable: Résultat non trouvé", 404);
        }

        $jury_examinateur = $jury->examinateur_id;
        if ($jury_examinateur == $examinateurId) {
            if (is_numeric($examinateurId)) {
                $params = [
                    'jury_id' => $juryId,
                    'examen_id' => $examenId,
                ];

                $path = "jury/dossiers/";
                $response =  Api::base('POST', $path, $params);

                if ($response->ok()) {
                    $responseData = $response->json();
                    return $this->successResponseclient($responseData, 'Success', 200);
                } else {
                    $errorData = $response->json();
                    $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération';
                    $errors = $errorData['errors'] ?? null;
                    return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
                }
            } else {
                return $examinateurId;
            }
        } else {
            return $this->errorResponse("Vous n'êtes pas autorisé à administrer ce jury", 404);
        }
    }
    public function getNotedDossierbyJury(Request $request)
    {
        $examinateurId = $this->getExaminateurId();
        // Obtenez l'examinateur_id à partir de la table Jurie en utilisant le jury_id de la requête
        $juryId = $request->input('jury_id');
        $examenId = $request->input('examen_id');
        $jury = Jurie::find($juryId);
        // Vérifiez si le jury existe
        if (!$jury) {
            return $this->errorResponse("Jury introuvable: Résultat non trouvé", 404);
        }

        $jury_examinateur = $jury->examinateur_id;
        if ($jury_examinateur == $examinateurId) {
            if (is_numeric($examinateurId)) {
                $params = [
                    'jury_id' => $juryId,
                    'examen_id' => $examenId,
                ];

                $path = "jury/dossiers-noter/";
                $response =  Api::base('POST', $path, $params);

                if ($response->ok()) {
                    $responseData = $response->json();
                    return $this->successResponseclient($responseData, 'Success', 200);
                } else {
                    $errorData = $response->json();
                    $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération';
                    $errors = $errorData['errors'] ?? null;
                    return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
                }
            } else {
                return $examinateurId;
            }
        } else {
            return $this->errorResponse("Vous n'êtes pas autorisé à administrer ce jury", 404);
        }
    }

    public function recapts(Request $request)
    {
        $examinateurId = $this->getExaminateurId();
        $juryId = $request->input('jury_id');
        $examenId = $request->input('examen_id');

        if (is_numeric($examinateurId)) {
            $params = [
                'examinateur_id' => $examinateurId,
                'jury_id' => $juryId,
                'examen_id' => $examenId,
            ];

            return $this->exportFromBase("conduite-inspections/recapts", $params);
        } else {
            return $examinateurId;
        }
    }

    public function agendas(Request $request)
    {
        $examinateurId = $this->getExaminateurId();
        $juryId = $request->input('jury_id');
        $examenId = $request->input('examen_id');

        if (is_numeric($examinateurId)) {
            $params = [
                'examinateur_id' => $examinateurId,
                'jury_id' => $juryId,
                'examen_id' => $examenId,
            ];

            return $this->exportFromBase("conduite-inspections/agendas", $params);
        } else {
            return $examinateurId; // Renvoyer le message d'erreur
        }
    }
    public function vague(Request $request)
    {
        $examinateurId = $this->getExaminateurId();
        $juryId = $request->input('jury_id');
        $examenId = $request->input('examen_id');


        if (is_numeric($examinateurId)) {
            $params = [
                'examinateur_id' => $examinateurId,
                'jury_id' => $juryId,
                'examen_id' => $examenId,
            ];

            return $this->exportFromBase("conduite-inspections/vagues", $params);
        } else {
            return $examinateurId; // Renvoyer le message d'erreur
        }
    }

    public function closeJury(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jury_id' => 'required|exists:juries,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }
            $examen = Examen::find($request->get('examen_id'));
            if (!$examen) {
                return $this->errorResponse("Jury introuvable: Résultat non trouvé", 404);
            }
            $examenId = $request->input('examen_id');
            $juryId = $request->input('jury_id');

            $jury = Jurie::find($juryId);

            if (!$jury) {
                return $this->errorResponse("Jury introuvable: Résultat non trouvé", 404);
            }

            $examen = Examen::find($examenId);
            // Vérifier si l'examen est déjà fermé
            if ($examen->closed) {
                return $this->errorResponse("L'examen associé est déjà fermé.", 409);
            }

            $jury->update([
                'closed' => true,
            ]);

            // Vérifier s'il reste des jurys non fermés pour l'examen
            if (Jurie::where('closed', false)->where('examen_id', $examenId)->count() === 0) {
                // Vérification des candidats utilisant la session actuelle
                $annexes = AnnexeAnatt::all();
                foreach ($annexes as $annexe) {
                    $dossierSessions = DossierSession::where('examen_id', $examenId)
                        ->where('annexe_id', $annexe->id)
                        ->where(function ($query) {
                            $query->whereNull('closed')
                                ->orWhere('closed', false);
                        })
                        ->whereNull('presence')
                        ->whereNull('presence_conduite')
                        ->where('state', 'validate')
                        ->where('abandoned', false)
                        ->exists();

                    if ($dossierSessions) {
                        return $this->errorResponse("Il existe des candidats pour l'annexe " . $annexe->name . " utilisant la session actuelle. L'examen ne peut pas être fermé.", 409);
                    }
                }
                // Si aucun candidat n'utilise la session actuelle, alors fermer l'examen
                $examen->update([
                    'closed' => true,
                ]);
            }

            return $this->successResponse($jury, 'Jurie fermée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    public function verifyCandidat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $convocation = ConvocationConduite::where('code', $request->code)->first();

        if (!$convocation) {
            return $this->errorResponse("Candidat non identifié", 404);
        }

        $candidat = GetCandidat::findOne($convocation->dossierSession->npi);

        $juryCandidat = JuryCandidat::where('dossier_session_id', $convocation->dossier_session_id)->latest()->first();

        $candidat['jury_candidat_id'] = $juryCandidat->id;
        return $this->successResponse($candidat, "Code de convocation valide", 200);
    }
}
