<?php

namespace App\Http\Controllers;

use App\Services\Api;
use App\Models\Inspecteur;
use App\Models\AnnexeAnatt;
use App\Models\Examinateur;
use Illuminate\Http\Request;
use App\Models\InspecteurSalle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Models\ExaminateurCategoriePermis;

class ExaminateurController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/examinateurs",
     *     operationId="getAllExaminateurs",
     *     tags={"Examinateurs"},
     *     summary="Récupérer la liste des examinateurs",
     *     description="Récupère une liste de tous les examinateurs enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des examinateurs récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'examinateurs",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="user_id",
     *                      description="ID de l'utilisateur associé à l'inspecteur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="annexe_anatt_id",
     *                      description="ID de l'annexe associé à l'inspecteur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="agent_id",
     *                      description="ID de l'agent ayant associé l'inspecteur",
     *                      type="integer"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $this->hasAnyPermission(["all","read-inspections","edit-inspections"]);
        $inspecteurs = Examinateur::with(['user', 'annexe', 'examinateurCategoriePermis'])
            ->orderBy('id', 'desc') // Trie par ID en ordre décroissant
            ->get();
        return $this->successResponse($inspecteurs);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        DB::beginTransaction(); // Début de la transaction

        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|unique:examinateurs,user_id',
                'annexe_anatt_id' => 'required|integer',
                'categorie_permis_ids' => 'required|array|min:1',
            ]);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }

            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $agent_id = $user->id;
            $validatedData = $validator->validated();
            $validatedData['agent_id'] = $agent_id;

            $user_id = $request->input('user_id');
                $existingRecord = Examinateur::where('user_id', $user_id)
                    ->first();
                if ($existingRecord) {
                    DB::rollBack(); // Annuler la transaction en cas d'erreur
                    return $this->errorResponse('Cet examinateur existe déjà', null, null, 422);
                }

            $examinateur = Examinateur::create($validatedData);

            $categorie_permis_ids = [];
            foreach ($validatedData['categorie_permis_ids'] as $categorie_permis_id) {
                $categorie_permis_ids[] = [
                    'examinateur_id' => $examinateur->id,
                    'categorie_permis_id' => $categorie_permis_id,
                ];
            }

            ExaminateurCategoriePermis::insert($categorie_permis_ids);

            DB::commit(); // Valider la transaction si tout s'est bien passé

            $result = Examinateur::with('examinateurCategoriePermis')->findOrFail($examinateur->id);
            return $this->successResponse($result, statuscode: 201);
        } catch (\Throwable $e) {
            DB::rollBack(); // Annuler la transaction en cas d'exception
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création", null, null, 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        $inspecteur = Examinateur::find($id);
        if (!$inspecteur) {
            return $this->errorResponse('Inspecteur introuvable', null, null, 422);
        }
        return $this->successResponse($inspecteur);
    }


    public function examinateursByAnnexe($annexe_id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            $inspecteurs = Examinateur::with(['user', 'annexe'])
                ->where('annexe_anatt_id', $annexe_id)
                ->orderBy('id', 'desc')
                ->get();

            return $this->successResponse($inspecteurs);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            $validator = Validator::make($request->all(), [
                'annexe_anatt_id' => 'required|integer',
                'categorie_permis_ids' => 'required|array|min:1'
            ]);

            // Obtenir l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $agent_id = $user->id; // Récupérer l'ID de l'utilisateur connecté

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }

            // Vérifier si l'examinateur existe
            $examinateur = Examinateur::find($id);

            if (!$examinateur) {
                return $this->errorResponse('Examinateur introuvable', null, null, 404);
            }

            // Mettez à jour les champs annexe_anatt_id
            $examinateur->annexe_anatt_id = $validator->validated()['annexe_anatt_id'];
            $examinateur->save();

            // Mettez à jour les catégories de permis associées
            $newCategoriePermisIds = $validator->validated()['categorie_permis_ids'];

            // Supprimer les anciennes associations de catégories de permis
            $examinateur->examinateurCategoriePermis()->delete();

            // Créez les nouvelles associations de catégories de permis
            $categorie_permis_ids = [];
            foreach ($newCategoriePermisIds as $categorie_permis_id) {
                $categorie_permis_ids[] = [
                    'examinateur_id' => $examinateur->id,
                    'categorie_permis_id' => $categorie_permis_id
                ];
            }
            ExaminateurCategoriePermis::insert($categorie_permis_ids);

            // Récupérez l'examinateur mis à jour avec les nouvelles associations
            $result = Examinateur::with('examinateurCategoriePermis')->findOrFail($examinateur->id);

            return $this->successResponse($result, statuscode: 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            // Récupérer l'examinateur
            $examinateur = Examinateur::find($id);
            if (!$examinateur) {
                return $this->errorResponse('Examinateur introuvable', null, null, 404);
            }
            ExaminateurCategoriePermis::where('examinateur_id', $id)->delete();
            $examinateur->delete();
            return $this->successResponse(['message' => 'Suppression effectuée']);
        } catch (QueryException $e) {
            // Vérifier si c'est une violation de clé étrangère
            if ($e->errorInfo[0] == '23503') {
                return $this->errorResponse("Impossible de supprimer l'examinateur car il est associé à d'autres enregistrements.", null, null, 422);
            }
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression", null, null, 500);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression", null, null, 500);
        }
    }


}
