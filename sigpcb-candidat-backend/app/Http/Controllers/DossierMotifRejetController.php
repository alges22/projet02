<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\DossierMotifRejet;
use Illuminate\Support\Facades\Validator;

class DossierMotifRejetController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/dossier-motif-rejets",
     *      operationId="getDossierMotifRejets",
     *      tags={"DossierMotifRejets"},
     *      summary="Obtient la liste des dossier-motif-rejets des candidats",
     *      description="Obtient la liste des dossier-motif-rejets des candidats enregistrés dans la base de données",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des dossier-motif-rejets des candidats",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID des dossier-motif-rejets des candidats",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="motif",
     *                      description="le motif",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="date_rejet",
     *                      description="Date de rejet",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *                  @OA\Property(
     *                      property="date_soumission",
     *                      description="Date de soumission",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *                  @OA\Property(
     *                      property="date_decision",
     *                      description="Date de decision",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *              )
     *          )
     *      )
     * )
     */   
    public function index()
    {
        try {
            $dossier_motif_rejets = DossierMotifRejet::all();
            return $this->successResponse($dossier_motif_rejets);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des motifs de rejet des dossiers');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *      path="/api/anatt-candidat/dossier-motif-rejets",
     *      operationId="createDossierMotifRejet",
     *      tags={"DossierMotifRejets"},
     *      summary="Enrégistrer un nouveau dossier-motif-rejets du candidat",
     *      description="Crée un nouveau dossier-motif-rejets du candidat enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="motif",
     *                      description="le motif",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="date_rejet",
     *                      description="Date de rejet",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *                  @OA\Property(
     *                      property="date_soumission",
     *                      description="Date de soumission",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *                  @OA\Property(
     *                      property="date_decision",
     *                      description="Date de decision",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau dossier-motif-rejets du candidat créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouveau dossier-motif-rejets du candidat créé",
     *                  type="integer",
     *              ),
     *      )
     * )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'motif' => 'required',
                'dossier_candidat_id' => 'required',
                'date_rejet' => 'nullable|date',
                'date_soumission' => 'nullable|date',
                'date_decision' => 'nullable|date'
            ]);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }
            $dossier_motif_rejet = DossierMotifRejet::create($request->all());
            return $this->successResponse($dossier_motif_rejet, "Motif de rejet ajouté avec succès");
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la création du motif de rejet');
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/dossier-motif-rejets/{id}",
     *      operationId="getDossierMotifRejetById",
     *      tags={"DossierMotifRejets"},
     *      summary="Affiche les détails d'un dossier-motif-rejets du candidat",
     *      description="Affiche les détails d'un dossier-motif-rejets du candidat enregistré dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du dossier-motif-rejets du candidat à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Détails du dossier-motif-rejets du candidat récupéré",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du dossier-motif-rejets du candidat",
     *                  type="integer",
     *                  example=1
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="dossier-motif-rejets du candidat non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $dossier_motif_rejet = DossierMotifRejet::findOrFail($id);
            return $this->successResponse($dossier_motif_rejet);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Le motif de rejet demandé est introuvable');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *      path="/api/anatt-candidat/dossier-motif-rejets/{id}",
     *      operationId="updateDossierMotifRejet",
     *      tags={"DossierMotifRejets"},
     *      summary="Mettre à jour un dossier-motif-rejets du candidat",
     *      description="Met à jour un dossier-motif-rejets du candidat enregistré dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du dossier-motif-rejets du candidat",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="motif",
     *                      description="le motif",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="date_rejet",
     *                      description="Date de rejet",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *                  @OA\Property(
     *                      property="date_soumission",
     *                      description="Date de soumission",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *                  @OA\Property(
     *                      property="date_decision",
     *                      description="Date de decision",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="dossier-motif-rejets du candidat mis à jour",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du dossier-motif-rejets du candidat mis à jour",
     *                  type="integer",
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="dossier-motif-rejets du candidat non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $dossier_motif_rejet = DossierMotifRejet::findOrFail($id);
            $validator = Validator::make($request->all(), [
                    'motif' => 'required',
                    'dossier_candidat_id' => 'required',
                    'date_rejet' => 'nullable|date',
                    'date_soumission' => 'nullable|date',
                    'date_decision' => 'nullable|date'
                ]);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }
            $dossier_motif_rejet->update($request->all());
            return $this->successResponse($dossier_motif_rejet);
            } 
            catch (\Throwable $e){
                logger()->error($e);
                return $this->errorResponse('Une erreur est survenue lors de la mise à jour du motif de rejet');
            }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Delete(
     *      path="/api/anatt-candidat/dossier-motif-rejets/{id}",
     *      operationId="deleteDossierMotifRejet",
     *      tags={"DossierMotifRejets"},
     *      summary="Supprime un dossier-motif-rejets du candidat existant",
     *      description="Supprime un dossier-motif-rejets du candidat spécifié de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du dossier-motif-rejets du candidat à supprimer",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="dossier-motif-rejets du candidat supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="dossier-motif-rejets du candidat non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $dossier_motif_rejet = DossierMotifRejet::findOrFail($id);
            $dossier_motif_rejet->delete();
            return $this->successResponse('Le motif de rejet a été supprimé avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la suppression du motif de rejet');
        }
    }
}
