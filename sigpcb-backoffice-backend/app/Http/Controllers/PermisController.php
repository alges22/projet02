<?php

namespace App\Http\Controllers;

use App\Models\Permis;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PermisController extends ApiController
{
    
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/permis",
     *     operationId="getAllPermis",
     *     tags={"Permis"},
     *     summary="Récupérer la liste des permis",
     *     description="Récupère une liste de tous les permis enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des permis récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du permis",
     *                      type="integer"
     *                  ),
     *              @OA\Property(
     *                  property="candidat_id",
     *                  description="L'identifiant du candidat associé au permis",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="npi",
     *                  description="Le NPI du candidat",
     *                  type="string",
     *                  example="NPI123456"
     *              ),
     *              @OA\Property(
     *                  property="num",
     *                  description="Le numéro du permis",
     *                  type="string",
     *                  example="NPI123456"
     *              ),
     *              @OA\Property(
     *                  property="categorie_permis_id",
     *                  description="L'identifiant de la catégorie de permis",
     *                  type="integer",
     *                  example=2
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  description="Le statut du permis",
     *                  type="boolean",
     *              ),
     *              @OA\Property(
     *                  property="dossier_candidat_id",
     *                  description="L'identifiant du dossier candidat associé au permis",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="date_delivrance",
     *                  description="La date de délivrance du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2022-03-15"
     *              ),
     *              @OA\Property(
     *                  property="date_expiration",
     *                  description="La date d'expiration du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2027-03-15"
     *              ),
     *              @OA\Property(
     *                  property="date_reussite_conduite",
     *                  description="La date de réussite de l'examen de conduite",
     *                  type="string",
     *                  format="date",
     *                  example="2022-03-10"
     *              ),
     *              @OA\Property(
     *                  property="date_retrai",
     *                  description="La date de retrait du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2032-03-15"
     *              ),
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $permis = Permis::all();
            return $this->successResponse('Liste des permis récupérée avec succès', $permis);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des permis', 500);
        }
    }


    /**
     * @OA\Post(
     *      path="/api/anatt-admin/permis",
     *      operationId="createPermis",
     *      tags={"Permis"},
     *      summary="Crée un nouveau permis",
     *      description="Crée un nouveau permis enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="candidat_id",
     *                  description="L'identifiant du candidat associé au permis",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="num",
     *                  description="Le numéro du permis",
     *                  type="string",
     *                  example="NPI123456"
     *              ),
     *              @OA\Property(
     *                  property="npi",
     *                  description="Le NPI du candidat",
     *                  type="string",
     *                  example="NPI123456"
     *              ),
     *              @OA\Property(
     *                  property="categorie_permis_id",
     *                  description="L'identifiant de la catégorie de permis",
     *                  type="integer",
     *                  example=2
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  description="Le statut du permis",
     *                  type="boolean",
     *              ),
     *              @OA\Property(
     *                  property="dossier_candidat_id",
     *                  description="L'identifiant du dossier candidat associé au permis",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="date_delivrance",
     *                  description="La date de délivrance du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2022-03-15"
     *              ),
     *              @OA\Property(
     *                  property="date_expiration",
     *                  description="La date d'expiration du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2027-03-15"
     *              ),
     *              @OA\Property(
     *                  property="date_reussite_conduite",
     *                  description="La date de réussite de l'examen de conduite",
     *                  type="string",
     *                  format="date",
     *                  example="2022-03-10"
     *              ),
     *              @OA\Property(
     *                  property="date_retrai",
     *                  description="La date de retrait du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2032-03-15"
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau Permis créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $request = $request->merge([
            'agent_id' => auth()->id()
        ]);
        return $this->postToBase("permis", $request->all());
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-admin/candidats-permis/{candidatId}",
     *      operationId="getCandidatPermisById",
     *      tags={"Permis"},
     *      summary="Récupère les permis d'un candidat par son ID",
     *      description="Récupère les permis enregistré pour un candidat dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="candidatId",
     *          description="ID du candidat",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Permis récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Permis non trouvé"
     *      )
     * )
     */
    public function getPermisByCandidatId($candidatId)
    {
        try {
            $permis = Permis::where('candidat_id', $candidatId)->get();

            return $this->successResponse($permis, 'Liste des permis du candidat récupérée avec succès',);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des permis du candidat', 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-admin/candidat-permis/{candidatId}/{permisPrealableId}",
     *      operationId="getCandidatPermiById",
     *      tags={"Permis"},
     *      summary="Récupère un permis d'un candidat par son ID et l'ID du permis préalable",
     *      description="Récupère les informations d'un permis spécifique enregistré pour un candidat dans la base de données en spécifiant l'ID du candidat et l'ID du permis préalable.",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="candidatId",
     *          description="ID du candidat",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="permisPrealableId",
     *          description="ID du permis préalable",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK. La combinaison candidat_id et permis_prealable_id existe.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  description="Message de succès",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  description="Données du permis",
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found. La combinaison candidat_id et permis_prealable_id n'existe pas.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  description="Message d'erreur",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  description="Données nulles",
     *                  type="null"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error. Une erreur s'est produite lors de la récupération du permis.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  description="Message d'erreur",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  description="Données nulles",
     *                  type="null"
     *              )
     *          )
     *      )
     * )
     */
    public function checkPermisCombination($candidatId, $permisPrealableId)
    {
        try {
            $permis = Permis::where('candidat_id', $candidatId)
                ->where('categorie_permis_id', $permisPrealableId)
                ->get();

            if ($permis->isEmpty()) {
                return $this->errorResponse('Pas de résultat trouvé', null, null, 422);
            }

            return $this->successResponse($permis);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération de la liste.', null, null, 500);
        }
    }


    /**
     * @OA\Put(
     *      path="/api/anatt-admin/permis/{id}",
     *      operationId="updatePermis",
     *      tags={"Permis"},
     *      summary="Met à jour un permis existant",
     *      description="Met à jour un permis existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du permis à mettre à jour",
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
     *              @OA\Property(
     *                  property="candidat_id",
     *                  description="L'identifiant du candidat associé au permis",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="num",
     *                  description="Le numéro du permis",
     *                  type="string",
     *                  example="NPI123456"
     *              ),
     *              @OA\Property(
     *                  property="npi",
     *                  description="Le NPI du candidat",
     *                  type="string",
     *                  example="NPI123456"
     *              ),
     *              @OA\Property(
     *                  property="categorie_permis_id",
     *                  description="L'identifiant de la catégorie de permis",
     *                  type="integer",
     *                  example=2
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  description="Le statut du permis",
     *                  type="boolean",
     *              ),
     *              @OA\Property(
     *                  property="dossier_candidat_id",
     *                  description="L'identifiant du dossier candidat associé au permis",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="date_delivrance",
     *                  description="La date de délivrance du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2022-03-15"
     *              ),
     *              @OA\Property(
     *                  property="date_expiration",
     *                  description="La date d'expiration du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2027-03-15"
     *              ),
     *              @OA\Property(
     *                  property="date_reussite_conduite",
     *                  description="La date de réussite de l'examen de conduite",
     *                  type="string",
     *                  format="date",
     *                  example="2022-03-10"
     *              ),
     *              @OA\Property(
     *                  property="date_retrai",
     *                  description="La date de retrait du permis",
     *                  type="string",
     *                  format="date",
     *                  example="2032-03-15"
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Permis mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Permis non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'num' => 'required|unique:permis,num,' . $id . ',id,categorie_permis_id,' . $request->input('categorie_permis_id'),
                'candidat_id' => 'required|integer',
                'npi' => 'required',
                'categorie_permis_id' => 'required|integer',
                'status' => 'required',
                'dossier_candidat_id' => 'required|integer',
                'date_delivrance' => 'required|date',
                'date_expiration' => 'required|date',
                'date_reussite_conduite' => 'required|date',
                'date_retrai' => 'required|date',
            ], [
                'num.required' => 'Le champ numéro est obligatoire.',
                'num.unique' => 'Le numéro du permis existe déjà pour cette catégorie de permis.',
                'candidat_id.required' => 'Le champ candidat est obligatoire.',
                'candidat_id.integer' => 'Le champ candidat doit être un entier.',
                'npi.required' => 'Le champ npi est obligatoire.',
                'categorie_permis_id.required' => 'Le champ categorie permis est obligatoire.',
                'categorie_permis_id.integer' => 'Le champ categorie permis doit être un entier.',
                'status.required' => 'Le champ status est obligatoire.',
                'dossier_candidat_id.required' => 'Le champ dossier candidat est obligatoire.',
                'dossier_candidat_id.integer' => 'Le champ dossier candidat doit être un entier.',
                'date_delivrance.required' => 'Le champ date de delivrance est obligatoire.',
                'date_delivrance.date' => 'Le champ date de delivrance doit être une date valide.',
                'date_expiration.required' => 'Le champ date d\'expiration est obligatoire.',
                'date_expiration.date' => 'Le champ date d\'expiration doit être une date valide.',
                'date_reussite_conduite.required' => 'Le champ date de réussite de conduite est obligatoire.',
                'date_reussite_conduite.date' => 'Le champ date de réussite de conduite doit être une date valide.',
                'date_retrai.required' => 'Le champ date de retrait est obligatoire.',
                'date_retrai.date' => 'Le champ date de retrait doit être une date valide.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $permis = Permis::findOrFail($id);
            $permis->update($request->all());
            return $this->successResponse($permis, 'Permis mis à jour avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour du permis", null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-admin/permis/{id}",
     *      operationId="getPermisById",
     *      tags={"Permis"},
     *      summary="Récupère un permis par ID",
     *      description="Récupère un permis enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du permis à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Permis récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Permis non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $permis = Permis::find($id);
            if (!$permis) {
                return $this->errorResponse("Permis non trouvé");
            }
            return $this->successResponse($permis);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur s\'est produite lors de la récupération du permis");
        }
    }


    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/permis/{id}",
     *      operationId="deletePermis",
     *      tags={"Permis"},
     *      summary="Supprime un permis",
     *      description="Supprime un permis de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du permis à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Permis supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Permis non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $permis = Permis::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Permis non trouvé', [], null, 422);
            }
            $permis->delete();
            return $this->successResponse(['message' => 'Permis supprimé avec succès']);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s\'est produite lors de la suppression du permis");
        }
    }
}
