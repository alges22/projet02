<?php

namespace App\Http\Controllers;
use Throwable;
use App\Services\Api;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class BaseAgregateursController extends ApiController
{
        /**
         * @OA\Get(
         *     path="/api/anatt-admin/agregateurs",
         *     operationId="getAllAgregateurs",
         *     tags={"BaseAgregateurs"},
         *     summary="Récupérer la liste des agregateurs",
         *     description="Récupère une liste de tous les agregateurs enregistrés dans la base de données",
         *     @OA\Response(
         *         response="200",
         *         description="La liste des agregateurs récupéré avec succès",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(
         *                  type="object",
         *                  @OA\Property(
         *                      property="id",
         *                      description="ID de l'agregateur",
         *                      type="integer"
         *                  ),
         *                  @OA\Property(
         *                      property="name",
         *                      description="Nom de l'agregateur",
         *                      type="string"
         *                  ),
         *                  @OA\Property(
         *                      property="photo",
         *                      description="Une image de l'agregateur",
         *                      type="string"
         *                  ),
         *                  @OA\Property(
         *                      property="status",
         *                      description="Statut de l'agregateur (optionnel)",
         *                      type="boolean"
         *                  )
         *              )
         *         )
         *     )
         * )
         */
        public function index()
        {
                try {
                    $path = "agregateurs";
                    $response =  Api::base('GET', $path);
                        if ($response->ok()) {
                        // Obtenir les données de la réponse
                        $data = $response->json();

                        // Retourner une réponse de succès avec les données
                        return $this->successResponseclient($data);
                        } else {
                        // Retourner une réponse d'erreur si la requête a échoué
                        return $this->errorResponseclient('Une erreur s\'est produite lors de la récupération des informations.',null,null, 422);
                        }
                } catch (Throwable $th) {
                logger()->error($th);
                        // Retourner une réponse d'erreur en cas d'exception
                        return $this->errorResponseclient('Une erreur s\'est produite lors de la récupération des informations.', 500);
                }
        }

        /**
         * @OA\Post(
         *      path="/api/anatt-admin/agregateurs",
         *      operationId="createAgregateurs",
         *      tags={"BaseAgregateurs"},
         *      summary="Crée un nouveau agregateur de conduite",
         *      description="Crée un nouveau agregateur de conduite enregistré dans la base de données",
         *      @OA\RequestBody(
         *          required=true,
         *          @OA\JsonContent(
         *                  @OA\Property(
         *                      property="name",
         *                      description="Nom de l'agregateur",
         *                      type="string"
         *                  ),
         *                  @OA\Property(
         *                      property="photo",
         *                      description="Une image de l'agregateur",
         *                      type="string"
         *                  ),
         *                  @OA\Property(
         *                      property="status",
         *                      description="Statut de l'agregateur (optionnel)",
         *                      type="boolean"
         *                  )
         *          )
         *      ),
         *      @OA\Response(
         *          response=200,
         *          description="Nouveau agregateur de conduite créé"
         *      )
         * )
         */
        public function store(Request $request)
        {
            try {
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'photo' => 'required',
                    'status' => 'required',
                ], [
                    'name.required' => 'Le champ nom est obligatoire.',
                    'photo.required' => 'Le champ photo est obligatoire.',
                    'status.required' => 'Le champ statut est obligatoire.',
                ]);

                if ($validator->fails()) {
                    return $this->errorResponseclient("La validation a échoué.", $validator->errors(), null, 422);
                }

                $data = [
                    'name' => $request->input('name'),
                    'status' => $request->input('status'),
                    'photo' => $request->input('photo'),
                ];

                // Effectuer la requête POST à l'API en utilisant votre méthode d'assistance
                $response = Api::base('POST', 'agregateurs', $data);

                // Vérifier la réponse de l'API externe
                if ($response->ok()) {
                    $responseData = $response->json();
                    return $this->successResponseclient($responseData, 'Agrégateur créé avec succès.', 200);
                } else {
                    $errorData = $response->json();
                    $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la création de l\'agrégateur.';
                    $errors = $errorData['errors'] ?? null;
                    return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
                }
            } catch (\Throwable $th) {
                logger()->error($th);
                return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
            }
        }


        /**
         * @OA\Get(
         *      path="/api/anatt-admin/agregateurs/{id}",
         *      operationId="getAgregateursById",
         *      tags={"BaseAgregateurs"},
         *      summary="Récupère un agregateur par ID",
         *      description="Récupère un agregateur enregistré dans la base de données en spécifiant son ID",
         *      @OA\Parameter(
         *          name="id",
         *          description="ID de l'agregateur à récupérer",
         *          required=true,
         *          in="path",
         *          @OA\Schema(
         *              type="integer",
         *              format="int64"
         *          )
         *      ),
         *      @OA\Response(
         *          response=200,
         *          description="Agregateur récupéré avec succès"
         *      ),
         *      @OA\Response(
         *          response=404,
         *          description="Agregateur non trouvé"
         *      )
         * )
         */
        public function show($id)
        {
                try {
                        $path = "agregateurs/" . $id;
                        $response =  Api::base('GET', $path);
                        if ($response->ok()) {
                        $responseData = $response->json();
                        return $this->successResponseclient($responseData, 'Success', 200);
                        } else {
                        $errorData = $response->json();
                        $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération de l\'agrégateur.';
                        $errors = $errorData['errors'] ?? null;
                        return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
                        }
                } catch (\Throwable $th) {
                        logger()->error($th);
                        return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
                }
        }

        /**
         * @OA\Put(
         *      path="/api/anatt-admin/agregateurs/{id}",
         *      operationId="updateAgregateurs",
         *      tags={"BaseAgregateurs"},
         *      summary="Met à jour un agregateur existant",
         *      description="Met à jour un agregateur existant dans la base de données",
         *      @OA\Parameter(
         *          name="id",
         *          description="ID de l'agregateur à mettre à jour",
         *          required=true,
         *          in="path",
         *          @OA\Schema(
         *              type="integer",
         *              format="int64"
         *          )
         *      ),
         *      @OA\RequestBody(
         *          required=true,
         *          @OA\JsonContent(
         *                  @OA\Property(
         *                      property="name",
         *                      description="Nom de l'agregateur",
         *                      type="string"
         *                  ),
         *                  @OA\Property(
         *                      property="photo",
         *                      description="Une image de l'agregateur",
         *                      type="string"
         *                  ),
         *                  @OA\Property(
         *                      property="status",
         *                      description="Statut de l'agregateur (optionnel)",
         *                      type="boolean"
         *                  )
         *          )
         *      ),
         *      @OA\Response(
         *          response=200,
         *          description="Agregateur mis à jour avec succès"
         *      ),
         *      @OA\Response(
         *          response=404,
         *          description="Agregateur non trouvé"
         *      )
         * )
         */
        public function update(Request $request, $id)
        {
            try {
                $validator = Validator::make($request->all(), [
                    'name' => [
                        'required',
                    ],
                    'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
                    'status' => 'required|boolean',
                ], [
                    'name.required' => 'Le champ nom est obligatoire.',
                    'photo.file' => 'Le fichier doit être un fichier image.',
                    'photo.mimes' => 'Le fichier doit être au format JPEG, PNG ou JPG.',
                    'photo.max' => 'Le fichier doit être inférieur à 2 Mo.',
                    'status.required' => 'Le champ statut est obligatoire.',
                    'status.boolean' => 'Le champ statut doit être un booléen.',
                ]);

                if ($validator->fails()) {
                    return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
                }

                // Préparer les données à envoyer à l'API externe
                $data = [
                    'name' => $request->input('name'),
                    'status' => $request->input('status'),
                ];
                if ($request->hasFile('photo')) {
                    $data['photo'] = $request->file('photo');
                }
                // Effectuer la requête PUT à l'API externe
                $response = Api::base('PUT', 'agregateurs/' . $id, $data);
                // Vérifier la réponse de l'API externe
                if ($response->ok()) {
                    // Obtenir les données de la réponse
                    $responseData = $response->json();

                    // Retourner une réponse de succès avec les données
                    return $this->successResponseclient($responseData, 'Agrégateur mis à jour avec succès.', 200);
                } else {
                    // Retourner une réponse d'erreur si la requête a échoué
                    $errorData = $response->json();
                    $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la mise à jour de l\'agrégateur.';
                    $errors = $errorData['errors'] ?? null;
                    return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
                }
            } catch (\Throwable $th) {
                logger()->error($th);
                return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
            }
        }


        /**
         * @OA\Delete(
         *      path="/api/anatt-admin/agregateurs/{id}",
         *      operationId="deleteAgregateurs",
         *      tags={"BaseAgregateurs"},
         *      summary="Supprime un agregateur",
         *      description="Supprime un agregateur de la base de données",
         *      @OA\Parameter(
         *          name="id",
         *          description="ID de l'agregateur à supprimer",
         *          required=true,
         *          in="path",
         *          @OA\Schema(
         *              type="integer",
         *              format="int64"
         *          )
         *      ),
         *      @OA\Response(
         *          response=200,
         *          description="Agregateur supprimé avec succès"
         *      ),
         *      @OA\Response(
         *          response=404,
         *          description="Agregateur non trouvé"
         *      )
         * )
         */
        public function destroy($id)
        {
            try {
                $response = Api::base('DELETE', 'agregateurs/' . $id);

                if ($response->ok()) {
                    $responseData = $response->json();
                    return $this->successResponseclient($responseData, 'L\'agrégateur a été supprimé avec succès.', 200);
                } else {
                    $errorData = $response->json();
                    $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la suppression de l\'agrégateur.';
                    $errors = $errorData['errors'] ?? null;
                    return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
                }
            } catch (\Throwable $th) {
                logger()->error($th);
                return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
            }
        }
}
