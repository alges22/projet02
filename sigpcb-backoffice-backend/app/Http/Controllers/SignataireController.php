<?php

namespace App\Http\Controllers;

use App\Models\ActeSignable;
use App\Models\Signataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SignataireController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/signataires",
     *     operationId="getAllSignataires",
     *     tags={"Signataires"},
     *     summary="Récupérer la liste des signataires",
     *     description="Récupère une liste de tous les signataires enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des signataires récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du signataire",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="user_id",
     *                      description="ID de l'utilisateur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="fichier_signature",
     *                      description="Fichier signataire",
     *                      type="string"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $signataires = Signataire::with('user')
                                    ->orderBy('id', 'desc')
                                    ->get();

            return $this->successResponse($signataires);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }


    /**
     * @OA\Post(
     *      path="/api/anatt-admin/signataires",
     *      operationId="createSignataires",
     *      tags={"Signataires"},
     *      summary="Crée un signataire",
     *      description="Crée un nouveau signataire enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="user_id",
     *                      description="ID de l'utilisateur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="fichier_signature",
     *                      description="Fichier signature",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau signataire créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|unique:signataires',
                'fichier_signature' => 'image'
            ],
        [
            'user_id.required' => 'L\'utilisateur n\'est pas renseigné',
            'user_id.integer' => 'L\'utilisateur doit être un entier',
            'user_id.unique' => 'L\'utilisateur existe déjà',
            'fichier_signature.image' => 'Le fichier n\'est pas une image'
        ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $signataire = Signataire::create($validator->validated());

            return $this->successResponse($signataire, 'Signataire créé avec succès', 201);
        } catch (\Throwable $e) {
            // handle the exception and log the error
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création du signataire");
        }
    }

    /**
     * @OA\Put(
     *      path="/api/anatt-admin/signataires/{id}",
     *      operationId="updateSignataires",
     *      tags={"Signataires"},
     *      summary="Met à jour un signataire existant",
     *      description="Met à jour un signataire existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de le signataire à mettre à jour",
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
     *                      property="user_id",
     *                      description="ID de l'utilisateur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="fichier_signature",
     *                      description="Fichier signature",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Signataire mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Signataire non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'fichier_signature' => 'image'
            ],
        [
             'user_id.required' => 'L\'utilisateur est obligatoire',
        ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $signataire = Signataire::findOrFail($id);
            $signataire->update($validator->validated());
            return $this->successResponse($signataire, 'Signataire mise à jour avec succès');
        } catch (\Throwable $e) {
            // handle the exception and log the error
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-admin/signataires/{id}",
     *      operationId="getSignatairesById",
     *      tags={"Signataires"},
     *      summary="Récupère un signataire par ID",
     *      description="Récupère un signataire enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du signataire à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Signataire récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Signataire non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $signataire = Signataire::with('user')->find($id);
            if (!$signataire) {
                return $this->errorResponse('Signataire introuvable');
            }
            return $this->successResponse($signataire);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }
    
    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/signataires/{id}",
     *      operationId="deleteSignataires",
     *      tags={"Signataires"},
     *      summary="Supprime un signataire",
     *      description="Supprime un signataire de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du signataire à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Signataire supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Signataire non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $signataire = Signataire::find($id);
            if (!$signataire) {
                return $this->errorResponse('Signataire introuvable');
            }
            $signataire->delete();
            $data = ['message' => 'Signataire supprimé avec succès'];

            return $this->successResponse($data);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression");
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/signataires/assign-acte",
     *      operationId="assignActe",
     *      tags={"Signataires"},
     *      summary="Crée une assignation",
     *      description="Crée une assignation enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="signataire_id",
     *                      description="ID du signataire",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="acte_signable_id",
     *                      description="ID de l'acte signable",
     *                      type="integer"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle assignation créé"
     *      )
     * )
     */

    public function assignActe(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "signataire_id" => "required|integer",
                "acte_signable_id" => "required",
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("Validation échouée", $validator->errors());
            }
            $actesIds = explode(";", $request->acte_signable_id);
            // On s'assure que tous les actes existent
            $actesSignableExits = collect($actesIds)
                ->every(
                    fn ($id) => ActeSignable::where('id', $id)->exists()
                );

            if (!$actesSignableExits) {
                return $this->errorResponse('Vérifier que tous les actes existent');
            }
            $signataire = Signataire::findOrFail($request->signataire_id);
            /** @var Signataire $signataire */
            $signataire->acteSignables()->syncWithoutDetaching($request->acte_signable_id);
            return $this->successResponse(null, message: "Acte assigné au signataire");
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression");
        }
    }
}
