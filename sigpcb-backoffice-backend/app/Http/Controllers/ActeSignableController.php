<?php

namespace App\Http\Controllers;
use App\Models\Signataire;

use App\Models\ActeSignable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ActeSignableSignataire;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActeSignableController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/acte-signables",
     *     operationId="getAllActeSignables",
     *     tags={"ActeSignables"},
     *     summary="Récupérer la liste des acte-signables",
     *     description="Récupère une liste de tous les acte-signables enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des acte-signables récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'acte-signable",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'acte-signable",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'acte-signable (optionnel)",
     *                      type="boolean"
     *                  )
     *              )
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        try {
            $query = ActeSignable::query();

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower($searchTerm)]);
                });
            }

            $acte_signables = $query->orderByDesc('id')->get();
            $acte_signables = $query->with(['signataires', 'signataires.user'])->orderByDesc('id')->get();


            if ($acte_signables->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);
            }

            return $this->successResponse($acte_signables);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }


    /**
     * @OA\Post(
     *      path="/api/anatt-admin/acte-signables",
     *      operationId="createActeSignables",
     *      tags={"ActeSignables"},
     *      summary="Crée un acte-signables",
     *      description="Crée un nouveau acte-signables enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'acte-signables",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'acte-signables (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="is_one_signataire",
     *                      description="si l'acte est signé par plusieur signataire ou pas (optionnel)",
     *                      type="boolean"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau acte-signables créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:acte_signables',
                'status' => 'required|boolean',
                'is_one_signataire' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(),null,422);
            }

            $data = $validator->validated();
            $acte_signable = new ActeSignable();
            $acte_signable->name = $data['name'];
            $acte_signable->status = $data['status'];
            $acte_signable->is_one_signataire = $data['is_one_signataire'];
            $acte_signable->save();

            $result = ActeSignable::with('signataires')->findOrFail($acte_signable->id);

            return $this->successResponse($result, 'Enregistrement effectué avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de la création', null, null, 500);
        }
    }


    /**
     * @OA\Post(
     *      path="/api/anatt-admin/acte-signables/assign-signataire",
     *      operationId="createActeSignableSignataires",
     *      tags={"ActeSignableSignataires"},
     *      summary="Crée un acte-signable-signataire",
     *      description="Crée un nouveau acte-signable-signataire enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="acte_signable_id",
     *                      description="id de l'acte-signable",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="is_one_signataire",
     *                      description="si l'acte est signé par plusieur signataire ou pas (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="signataire_ids",
     *                      description="ID du ou des signataires",
     *                      type="integer"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau acte-signable-signataire créé"
     *      )
     * )
     */
    public function assign(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'acte_signable_id' => 'required',
                'is_one_signataire' => 'required|boolean',
                'signataire_ids' => 'required|array|min:1',
                'signataire_ids.*' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            $data = $validator->validated();


            if ($data['is_one_signataire'] && count($data['signataire_ids']) > 1) {
                return $this->errorResponse('Vous ne pouvez enregistrer qu\'un seul signataire pour cette opération');
            }

            $acte_signable = ActeSignable::findOrFail($data['acte_signable_id']);

            $acte_signable->signataires()->sync($data['signataire_ids']);

            $result = ActeSignable::with('signataires')->findOrFail($acte_signable->id);

            return $this->successResponse($result, 'Enregistrement effectué avec succès');
        } catch (ModelNotFoundException $e) {
            logger()->error($e);
            return $this->errorResponse('Acte signable non trouvé', null, null, 422);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de l\'affectation', null, null, 500);
        }
    }


    /**
     * @OA\Put(
     *      path="/api/anatt-admin/acte-signables/{id}",
     *      operationId="updateActeSignables",
     *      tags={"ActeSignables"},
     *      summary="Met à jour un acte-signable existant",
     *      description="Met à jour un acte-signable existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'acte-signable à mettre à jour",
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
     *                      description="Nom de l'acte-signables",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'acte-signables (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="is_one_signataire",
     *                      description="si l'acte est signé par plusieur signataire ou pas (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="signataire_ids",
     *                      description="l'id du ou des signataire",
     *                      type="integer"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Acte-signable mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Acte-signable non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $acte_signable = ActeSignable::with('signataires')->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('acte_signables')->ignore($acte_signable->id)
                ],
                'status' => 'required|boolean',
                'is_one_signataire' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $data = $validator->validated();
            $acte_signable->update($data);
            $result = ActeSignable::with('signataires')->findOrFail($id);

            return $this->successResponse($result, 'Mise à jour effectuée avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de la mise à jour', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-admin/acte-signables/{id}",
     *      operationId="getActeSignablesById",
     *      tags={"ActeSignables"},
     *      summary="Récupère un acte-signable par ID",
     *      description="Récupère un acte-signable enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'acte-signable à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="acte-signable récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="acte-signable non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $acte_signable = ActeSignable::with(['signataires','signataires.user'])->find($id);
            if (!$acte_signable) {
                return $this->errorResponse('Cet acte est introuvable', null, null, 422);
            }

            return $this->successResponse($acte_signable);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/acte-signables/{id}",
     *      operationId="deleteActeSignables",
     *      tags={"ActeSignables"},
     *      summary="Supprime un acte-signable",
     *      description="Supprime un acte-signable de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'acte-signable à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Acte-signable supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Acte-signable non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $acte_signable = ActeSignable::find($id);

            if (!$acte_signable) {
                return $this->errorResponse('Cet acte est introuvable', null, null, 422);
            }

            $acte_signable->signataires()->detach(); // Supprime les assignations de signataires
            $acte_signable->delete(); // Supprime l'acte signable

            return $this->successResponse(null, 'Cet acte est supprimé avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression");
        }
    }

    private function acteExist(string $acte_signable_ids)
    {
        $ids = explode(";", $acte_signable_ids);
        // Si tous les signataires exists
        return collect($ids)->every(fn ($id) => ActeSignable::whereId(intval($id))->exists());
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/acte-signables/status",
     *      operationId="createActeSignableStatus",
     *      tags={"ActeSignables"},
     *      summary="Désactivation ou activation d'un acte",
     *      description="Désactivation ou activation d'un acte",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="acte_signable_id",
     *                      description="id de l'acte",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut a modifier",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Mise à jour éffectué avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="l'utilisateur n'a pas été trouvé"
     *      )
     * )
     */
    public function status(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'acte_signable_id' => 'required',
                'status' => 'required'
            ], [
                'acte_signable_id.required' => 'Aucun acte n\'a été sélectionné',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }

            $acte_signable_id = $request->get('acte_signable_id');
            $status = $request->get('status');
            if (!$this->acteExist($acte_signable_id)) {
                return $this->errorResponse('Vérifiez que le signataire sélectionné existe', $validator->errors());
            }

            $acte_signable = ActeSignable::where('id', $acte_signable_id)->first();

            ActeSignable::where('id', $acte_signable_id)->update(['status' => $status]);
            $acte_signable = ActeSignable::findOrFail($acte_signable_id); // récupérer l'acte mis à jour
            return $this->successResponse(['acte_signable' => $acte_signable, 'message' => 'Mise à jour effectué avec succès']);
        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }
}
