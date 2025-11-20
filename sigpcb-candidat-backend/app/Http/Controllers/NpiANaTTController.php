<?php
namespace App\Http\Controllers;
use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\DossierSession;
use App\Models\DossierCandidat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Admin\Examen;

class NpiANaTTController extends ApiController
{


    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/npi-candidat",
     *     summary="Vérifie le numéro npi du candidat",
     *     description="Vérifie le numéro npi du candidat.",
     *     operationId="getNPI",
     *     tags={"AnipCandidat"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données pour vérifier le numéro npi du candidat",
     *         @OA\JsonContent(
     *             required={"npi"},
     *             @OA\Property(property="npi", type="integer", example="12345467890"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="vefification éffectuée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="véfirication éffectuée avec succès"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="vefification non éffectuée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example=""),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="vefification non éffectuée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation échouée."),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la vérification"),
     *         ),
     *     ),
     * )
     */
    public function getNPI(Request $request)
    {
        try {
            // Récupérer le numéro NPI de la requête
            $validator = Validator::make($request->all(), [
                'npi' => 'required|numeric',
            ],
            [
                "npi.required" => "Le champ  NPI ne peut pas être vide",
                "npi.numeric"  => "Le champ NPI doit contenir uniquement des chiffres"
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }

            $npi = $request->input('npi');

               // Effectuer la requête GET à l'autre API avec le numéro NPI
               $response = Api::base('GET', "candidats/" . $npi);

               // Vérifier la réponse de l'API externe
               if ($response !== -1 && $response->ok()) {
                   // Obtenir les données de la réponse
                   $data = $response->json();

                   // Extraire les informations spécifiques et les assigner à de nouvelles variables
                   $id = $data['data']['id'];
                   $nom = $data['data']['nom'];
                   $prenoms = $data['data']['prenoms'];
                   $email = $data['data']['email'];
                   $date_de_naissance = $data['data']['date_de_naissance'];
                   $lieu_de_naissance = $data['data']['lieu_de_naissance'];
                   $sexe = $data['data']['sexe'];
                   $adresse = $data['data']['adresse'];
                   $telephone = $data['data']['telephone'];
                   $npi = $data['data']['npi'];
                   $avatar = $data['data']['avatar'];
                   $telephone_prefix = $data['data']['telephone_prefix'];
                   $nationality = $data['data']['nationality'];

                    if (!empty($telephone)) {
                        $telephoneHash = $this->hashPhoneNumber($telephone);
                    }
                   // Construire le tableau de réponse avec les nouvelles variables
                   $responseData = [
                       'id' => $id,
                       'nom' => $nom,
                       'prenoms' => $prenoms,
                       'email' => $email,
                       'date_de_naissance' => $date_de_naissance,
                       'lieu_de_naissance' => $lieu_de_naissance,
                       'sexe' => $sexe,
                       'adresse' => $adresse,
                       'telephone' => $telephoneHash,
                       'npi' => $npi,
                       'avatar' => $avatar,
                       'telephone_prefix' => $telephone_prefix,
                       'nationality' => $nationality,
                   ];

                   // Retourner une réponse de succès avec les données mappées
                   return $this->successResponse($responseData);

               } else {
                   // Retourner une réponse d'erreur
                   return $this->errorResponse('Le numéro npi n\'existe pas.', 422);
               }
           } catch (\Throwable $e) {
               logger()->error($e);
               // Retourner une réponse d'erreur en cas d'exception
               return $this->errorResponse('Une erreur s\'est produite lors de la récupération des données NPI.', 500);
           }
    }


    private function hashPhoneNumber($telephone)
    {
        $length = strlen($telephone);
        if ($length <= 4) {
            return str_repeat('*', $length); // Retourne des astérisques si le numéro est court
        }

        // Conserve les 2 premiers et les 2 derniers chiffres
        return substr($telephone, 0, 2) . str_repeat('*', $length - 4) . substr($telephone, -2);
    }
       private function showExam($id)
       {
           try {
               $examen = Examen::findOrFail($id);
               return $this->successResponse($examen);
           } catch (ModelNotFoundException $exception) {
               return $this->errorResponse('Cet examen n\'a pas été trouvé.', [], null, 404);
           } catch (\Throwable $th) {
               logger()->error($th);
               return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!', [], null, 500);
           }
       }

    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/candidat/dossier-session",
     *      operationId="getUserDossierSession",
     *      tags={"AnipCandidat"},
     *      summary="Obtenir les informations du dossier de session d'un candidat",
     *      description="Obtenir les informations du dossier de session d'un candidat à partir de son numéro NPI.",
     *      @OA\Response(
     *          response=200,
     *          description="Le dossier session du candidat a été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Le dossier session du candidat n'a pas été trouvé"
     *      )
     * )
     */
    public function getUserDossierSession()
    {
        try {
            // Obtenir l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $id = $user->id; // Récupérer l'ID de l'utilisateur connecté
            $npi = $user->npi;

            // Effectuer la requête GET à l'autre API avec le numéro NPI
            $response = Api::base('GET', "candidats/" . $npi);

            // Vérifier la réponse de l'API externe
            if ($response !== -1 && $response->ok()) {
                // Obtenir les données de la réponse
                $data = $response->json();

                // Extraire les informations spécifiques et les assigner à de nouvelles variables
                $id = $data['data']['id'];
                $nom = $data['data']['nom'];
                $prenoms = $data['data']['prenoms'];
                $email = $data['data']['email'];
                $date_de_naissance = $data['data']['date_de_naissance'];
                $lieu_de_naissance = $data['data']['lieu_de_naissance'];
                $sexe = $data['data']['sexe'];
                $adresse = $data['data']['adresse'];
                $telephone = $data['data']['telephone'];
                $npi = $data['data']['npi'];
                $telephone_prefix = $data['data']['telephone_prefix'];

                // Construire le tableau

                // Récupérer l'ID de DossierSession associée
                $dossierSession = DossierSession::where('npi', $npi)
                                                  ->orderByDesc('created_at')
                                                  ->first();

                // Ajouter les données du dossier de session à la réponse même si elles n'existent pas
                $responseData = $dossierSession ?? null;


                if ($dossierSession) {
                    $dossierCandidatId = $dossierSession->dossier_candidat_id;
                    $dossierCandidat = DossierCandidat::find($dossierCandidatId);
                    $responseData['dossierCandidat'] = $dossierCandidat;
                }

                    // Si dossierSession existe et a un examen_id non null, récupérer les données de l'examen
                if ($dossierSession && $dossierSession->examen_id !== null) {
                    $response = $this->showExam($dossierSession->examen_id);
                    $responseData['examen'] = $response;
                } else {
                    // Ajouter une clé "examen" avec valeur null si l'examen n'existe pas
                    $responseData['examen'] = null;
                }


                // Retourner une réponse de succès avec les données mappées
                return $this->successResponse($responseData);

            } else {
                // Retourner une réponse d'erreur
                return $this->errorResponse('Le numéro npi n\'existe pas.', 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des données NPI.', 500);
        }
    }
}
