<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\Language;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ParametreController extends ApiController
{
        /**
     * @OA\Get(
     *     path="/api/anatt-base/parametres",
     *     operationId="getAllParametres",
     *     tags={"Parametres"},
     *     summary="Récupérer la liste des parametres",
     *     description="Récupère une liste de tous les parametres enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des parametres récupérés avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du parametre",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="delai_conduite",
     *                      description="delai pour la conduite",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="delai_ajounment_desertion",
     *                      description="delai pour les ajournement ou desertion",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="delai_correction_situation_ae",
     *                      description="delai pour la correction de la situation des auto ecoles",
     *                      type="string"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $parametres = Parametre::orderByDesc('id')->get();
            return $this->successResponse($parametres);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/anatt-base/parametres",
     *     operationId="createParametre",
     *     tags={"Parametres"},
     *     summary="Créer un nouveau parametre",
     *     description="Crée un nouveau parametre dans la base de données",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="delai_conduite",
     *                 description="delai pour la conduite",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="delai_ajounment_desertion",
     *                 description="delai pour les ajournement ou desertion",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="delai_correction_situation_ae",
     *                 description="delai pour la correction de la situation des auto ecoles",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Le parametre a été créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 description="ID du parametre",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="delai_conduite",
     *                 description="delai pour la conduite",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="delai_ajounment_desertion",
     *                 description="delai pour les ajournement ou desertion",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="delai_correction_situation_ae",
     *                 description="delai pour la correction de la situation des auto ecoles",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Les données fournies sont invalides",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 description="Erreur générée",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'delai_conduite' => 'required|string',
                'delai_ajounment_desertion' => 'required|string',
                'delai_correction_situation_ae' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Les données fournies sont invalides', 400);
            }

            $parametre = Parametre::create($request->all());

            return $this->successResponse($parametre, 'Le parametre a été créé avec succès', 201);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

        /**
     * @OA\Get(
     *      path="/api/anatt-base/parametres/{id}",
     *      operationId="getParametreById",
     *      tags={"Parametres"},
     *      summary="Récupère un parametre par ID",
     *      description="Récupère un parametre enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du parametre à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Parametre récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Parametre non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $parametre = Parametre::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le parametre avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($parametre);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

        /**
     * @OA\Put(
     *     path="/api/anatt-base/parametres/{id}",
     *     operationId="updateParametre",
     *     tags={"Parametres"},
     *     summary="Mettre à jour un parametre",
     *     description="Met à jour un parametre dans la base de données",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du parametre",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="delai_conduite",
     *                 description="delai pour la conduite",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="delai_ajounment_desertion",
     *                 description="delai pour les ajournement ou desertion",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="delai_correction_situation_ae",
     *                 description="delai pour la correction de la situation des auto ecoles",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Le parametre a été mis à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 description="Message de succès",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Le parametre n'a pas été trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 description="Message d'erreur",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Erreur de validation des données d'entrée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 description="Message d'erreur",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 description="Erreurs de validation",
     *                 type="object"
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $parametre = Parametre::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'delai_conduite' => 'required|string',
                'delai_ajounment_desertion' => 'required|string',
                'delai_correction_situation_ae' => 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Erreur de validation des données d\'entrée', $validator->errors(), 400);
            }

            $parametre->update($request->all());

            return $this->successResponse('Le paramètre a été mis à jour avec succès', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Le paramètre n\'a pas été trouvé', null, 404);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la mise à jour du paramètre', null, 500);
        }
    }


        /**
     * @OA\Delete(
     *      path="/api/anatt-base/parametres/{id}",
     *      operationId="deleteParametre",
     *      tags={"Parametres"},
     *      summary="Supprime un parametre",
     *      description="Supprime un parametre de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du parametre à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Parametre supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Parametre non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $parametre = Parametre::findOrFail($id);
            $parametre->delete();

            return $this->successResponse('Le paramètre a été supprimé avec succès', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Le paramètre n\'a pas été trouvé', null, 404);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la suppression du paramètre', null, 500);
        }
    }

}
