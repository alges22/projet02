<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;

use App\Services\Api;
use Illuminate\Http\Request;

class BaseChapitreController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/base/chapitres",
     *     operationId="getAllChapitre",
     *     tags={"BaseChapitre"},
     *     summary="Récupérer la liste des chapitres",
     *     description="Récupère une liste de tous les chapitres enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des agregateurs récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du chapitre",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="Nom du chapitre"),
     *                 ),
     *                 @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      description="Une description du chapitre"),
     *                ),
     *              )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {

        try {
            // Appel à l'API de l'instance 1 pour récupérer les informations des chapitres
            $response = Api::base('GET', 'chapitres', $request->all());

            if ($response->ok()) {
                $chapitresData = $response->json();
                return $this->successResponseclient($chapitresData);
            } else {
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération des données des chapitres.';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponse($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/base/chapitres",
     *      operationId="createchapitre",
     *      tags={"BaseChapitre"},
     *      summary="Crée un nouveau chapitre de conduite",
     *      description="Crée un nouveau chapitre de conduite enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nom du chapitre"),
     *             @OA\Property(property="description", type="string", description="Une description du chapitre"),
     *             @OA\Property(property="categorie_permis_ids", type="array", @OA\Items(type="integer"), description="IDs des catégories de permis associées au chapitre")
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau agregateur de conduite créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-base-settings"]);
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:chapitres,name',
                    'description' => 'nullable',
                    'categorie_permis_ids' => 'required|array|min:1',
                    'categorie_permis_ids.*' => 'required'
                ],
                [
                    'name.required' => 'Le champ name est requis.',
                    'name.unique' => 'Ce nom de chapitre existe déjà.',
                    'categorie_permis_ids.required' => 'Vous devez sélectionner au moins une catégorie de permis.',
                    'categorie_permis_ids.min' => 'Vous devez sélectionner au moins une catégorie de permis.',
                    'categorie_permis_ids.*.required' => 'Vous devez sélectionner au moins une catégorie de permis.',

                ]
            );

            if ($validator->fails()) {
                return $this->errorResponseclient("La validation a échoué.", $validator->errors(), null, 422);
            }

            $data = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'categorie_permis_ids' => $request->input('categorie_permis_ids'),
            ];

            // Effectuer la requête POST à l'API en utilisant votre méthode d'assistance
            $response = Api::base('POST', 'chapitres', $data);

            // Vérifier la réponse de l'API externe
            if ($response->ok()) {
                $responseData = $response->json();
                return $this->successResponseclient($responseData, 'Chapitre créé avec succès.', 200);
            } else {
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la création du Chapitre.';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
        }
    }



    public function show($id)
    {
        try {
            $path = "chapitres/" . $id;
            $response =  Api::base('GET', $path);

            if ($response->ok()) {
                $responseData = $response->json();
                return $this->successResponseclient($responseData, 'Success', 200);
            } else {
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
        }
    }





    public function destroy($id)
    {
        $this->hasAnyPermission(["all", "edit-base-settings"]);

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
