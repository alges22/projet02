<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\SalleCompo;
use Illuminate\Support\Facades\Validator;

class SalleCompoController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/salle-compos",
     *     operationId="getAllSalleCompos",
     *     tags={"SalleCompos"},
     *     summary="Récupérer la liste des salles de composition",
     *     description="Récupère une liste de tous les salles de composition enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des salles de composition récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la salle de composition",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la salle de composition",
     *                      type="string"
     *                  ),
     *                 @OA\Property(
     *                     property="annexe_anatt_id",
     *                     description="ID de l'annexe anatt auquel appartient la nouvelle salle créée",
     *                     type="integer"
     *                 ),
     *                  @OA\Property(
     *                      property="contenance",
     *                      description="Contenance de la salle de composition",
     *                      type="integer"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $salle_compos = SalleCompo::get();
            return $this->successResponse($salle_compos);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
     *      path="/api/anatt-base/salle-compos",
     *      operationId="createSalleCompo",
     *      tags={"SalleCompos"},
     *      summary="Crée une nouvelle salle de composition",
     *      description="Crée une nouvelle salle de composition enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la salle de composition",
     *                      type="string"
     *                  ),
     *                 @OA\Property(
     *                     property="annexe_anatt_id",
     *                     description="ID de l'annexe anatt auquel appartient la nouvelle salle créée",
     *                     type="integer"
     *                 ),
     *                  @OA\Property(
     *                      property="contenance",
     *                      description="Contenance de la salle de composition",
     *                      type="integer"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle salle de composition créée"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'unique:salle_compos,name'
                ],
                'annexe_anatt_id' => 'required',
                'contenance' => 'required'
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de la salle de composition existe déjà.',
                'annexe_anatt_id.required' => 'Le champ annexe annat est obligatoire.',
                'contenance.required' => 'Le champ contenance est obligatoire.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            $salle_compo = SalleCompo::create($request->all());
            return $this->successResponse($salle_compo, 'Salle de composition créée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }




    /**
     * @OA\Post(
     *      path="/api/anatt-base/salle-compo/multiple",
     *      operationId="createMultipleSalleCompo",
     *      tags={"SalleCompos"},
     *      summary="Crée plusieurs salles de composition",
     *      description="Crée plusieurs salles de composition enregistrées dans la base de données pour une annexe ANATT donnée",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="annexe_anatt_id",
     *                  description="ID de l'annexe ANATT à laquelle appartiennent les nouvelles salles créées",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="salle_comp_groupes",
     *                  description="Tableau contenant les informations sur les salles de composition à créer",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                          property="name",
     *                          description="Nom de la salle de composition",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="contenance",
     *                          description="Contenance de la salle de composition",
     *                          type="integer"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelles salles de composition créées"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation des données d'entrée"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur interne du serveur"
     *      )
     * )
     */
    public function storeMultiple(Request $request)
    {
        try {
            // Validation des données entrantes
            $validator = Validator::make($request->all(), [
                'annexe_anatt_id' => 'required|integer',
                'salle_comp_groupes' => 'required|array|min:1',
                'salle_comp_groupes.*.name' => [
                    'required',
                    'string',                   
                    Rule::unique('salle_compos')
                        ->where(function ($query) use ($request) {
                            return $query->where('annexe_anatt_id', $request->input('annexe_anatt_id'));
                        })
                ],
                'salle_comp_groupes.*.contenance' => 'required|integer',
            ], [
                'annexe_anatt_id.required' => 'L\'ID de l\'annexe anatt est obligatoire.',
                'annexe_anatt_id.integer' => 'L\'ID de l\'annexe anatt doit être un entier.',
                'annexe_anatt_id.exists' => 'L\'ID de l\'annexe anatt spécifié n\'existe pas dans la base de données.',
                'salle_comp_groupes.required' => 'Le champ salle_comp_groupes est obligatoire.',
                'salle_comp_groupes.array' => 'Le champ salle_comp_groupes doit être un tableau.',
                'salle_comp_groupes.min' => 'Le tableau salle_comp_groupes doit contenir au moins un élément.',
                'salle_comp_groupes.*.name.required' => 'Le champ nom est obligatoire.',
                'salle_comp_groupes.*.name.string' => 'Le champ nom doit être une chaîne de caractères.',
                'salle_comp_groupes.*.name.unique' => 'Le nom de la salle doit être unique pour chaque annexe anatt.',
                'salle_comp_groupes.*.contenance.required' => 'Le champ contenance est obligatoire.',
                'salle_comp_groupes.*.contenance.integer' => 'Le champ contenance doit être un entier.',
            ]);
    
            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }
            $salle_comp_groupes = collect($request->input('salle_comp_groupes'));
            $duplicates = $salle_comp_groupes->duplicates('name');
            if ($duplicates->count() > 0) {
                // Il y a des doublons
                return $this->errorResponse("Le nom de la salle doit être unique pour chaque annexe anatt.");
            }

            $annexe_anatt_id = $request->input('annexe_anatt_id');
            $salle_comp_groupes = $request->input('salle_comp_groupes');
            //Suppression des données 
            // SalleCompo::where('annexe_anatt_id', $annexe_anatt_id)->delete();
            // Enregistrement des salles de composition
            $salleCompos = collect($salle_comp_groupes)->map(function ($salle_comp_groupe) use ($annexe_anatt_id) {
                $salleCompo = new SalleCompo([
                    'name' => $salle_comp_groupe['name'],
                    'contenance' => $salle_comp_groupe['contenance']
                ]);
    
                $salleCompo->annexe_anatt_id = $annexe_anatt_id;
                $salleCompo->save();
    
                return $salleCompo;
            });
            return $this->successResponse($salleCompos, 'Les salles de composition ont été créées avec succès.');

        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
     *      path="/api/anatt-base/salle-compos/{id}",
     *      operationId="getSalleCompoById",
     *      tags={"SalleCompos"},
     *      summary="Récupère une salle de composition par ID",
     *      description="Récupère une salle de composition enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la salle de composition à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Salle de composition récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Salle de composition non trouvée"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $salle_compo = SalleCompo::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La salle de composition avec l\'ID '.$id.' n\'a pas été trouvée.', [], null, 404);
            }
            return $this->successResponse($salle_compo);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
     *      path="/api/anatt-base/annexeanatt-salle-compos/{annexe_anatt_id}",
     *      operationId="getAnnexeAnattSalleCompoById",
     *      tags={"SalleCompos"},
     *      summary="Récupère les salles d'une annexe anatt",
     *      description="Récupère les salles d'une annexe anatt enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="annexe_anatt_id",
     *          description="ID de l'annexe anatt dont on veut récuperer la salle",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Salle de composition récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Salle de composition non trouvée"
     *      )
     * )
     */
    public function getAnnexeAnattSalles($annexe_anatt_id)
    {
        try {
            // Récupération des salles de composition correspondantes à l'ID de l'annexe anatt
            $salle_compos = SalleCompo::where('annexe_anatt_id', $annexe_anatt_id)->orderByDesc('id')->get();
    
            // Vérification si les salles de composition existent
            if ($salle_compos->isEmpty()) {
                return $this->successResponse([],"Aucune salle de composition n'a été trouvée pour cet ID d'annexe anatt.");
            }
    
            // Renvoi des salles de composition dans une réponse JSON
            return $this->successResponse($salle_compos, 'Liste des salles de composition récupérées avec succès.');
    
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
     *      path="/api/anatt-base/salle-compos/{id}",
     *      operationId="updateSalleCompo",
     *      tags={"SalleCompos"},
     *      summary="Met à jour une salle de composition existante",
     *      description="Met à jour une salle de composition existante dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la salle de composition à mettre à jour",
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
     *                      description="Nom de la salle de composition",
     *                      type="string"
     *                  ),
     *                 @OA\Property(
     *                     property="annexe_anatt_id",
     *                     description="ID de l'annexe antt auquel appartient la nouvelle salle créée",
     *                     type="integer"
     *                 ),
     *                  @OA\Property(
     *                      property="contenance",
     *                      description="Contenance de la salle de composition",
     *                      type="integer"
     *                  )
     *             )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Salle de composition mise à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Salle de composition non trouvée"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('salle_compos')->where(function ($query) use ($request, $id) {
                        return $query->where('annexe_anatt_id', $request->input('annexe_anatt_id'))
                                     ->where('id', '!=', $id);
                    })
                ],
                'annexe_anatt_id' => 'required',
                'contenance' => 'required'
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de la salle de composition existe déjà pour cet annexe Anatt.',
                'annexe_anatt_id.required' => 'Le champ centre d\'examen est obligatoire.',
                'contenance.required' => 'Le champ contenance est obligatoire.',
            ]);
    
            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }
    
            try {
                $salle_compo = SalleCompo::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La salle de composition avec l\'ID '.$id.' n\'a pas été trouvée.', [], null, 404);
            }
            $salle_compo->update($request->all());
            return $this->successResponse($salle_compo, 'Salle de composition mise à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
     *      path="/api/anatt-base/salle-compos/{id}",
     *      operationId="deleteSalleCompo",
     *      tags={"SalleCompos"},
     *      summary="Supprime une salle de composition",
     *      description="Supprime une salle de composition de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la salle de composition à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Salle de composition supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Salle de composition non trouvée"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $salle_compo = SalleCompo::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La salle de composition avec l\'ID '.$id.' n\'a pas été trouvée.', [], null, 404);
            }
            $salle_compo->delete();
            return $this->successResponse($salle_compo, 'La salle de composition a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
