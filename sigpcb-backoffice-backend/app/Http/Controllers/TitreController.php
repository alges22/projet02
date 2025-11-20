<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Titre;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Type\Time;

class TitreController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/titres",
     *     operationId="getAllTitres",
     *     tags={"Titres"},
     *     summary="Récupérer la liste des titres",
     *     description="Récupère une liste de tous les titres enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des titres récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'titre",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'titre",
     *                      type="string"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $this->hasAnyPermission(["all","edit-titles-management","read-titles-management"]);
        try {
            $titres = Titre::orderBy('id', 'desc')->get();
            return $this->successResponse($titres);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    public function checkIfExists($Name)
    {
        $existing = Titre::whereRaw('LOWER(name) LIKE ?', [strtolower($Name)])->first();
        if ($existing) {
            return false;
        } else {
            return true;
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
     *      path="/api/anatt-admin/titres",
     *      operationId="createTitres",
     *      tags={"Titres"},
     *      summary="Crée un nouveau titre",
     *      description="Crée un nouveau titre enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'titre",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau titre créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-titles-management"]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'unique:titres,name'
                ],
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom du titre existe déjà.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), null, 422);
            }
            $Name = $request->input('name');
            $status = $request->input('status');
            if ($this->checkIfExists($Name)) {
                $titre = new Titre();
                $titre->name = $Name;
                $titre->status = $status;
                $titre->save();
            } else {
                return $this->errorResponse("Un titre portant ce nom existe déjà.", 'Un titre portant ce nom existe déjà.', null, 422);
            }
            return $this->successResponse($titre, 'Titre créé avec succès.');
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
     *      path="/api/anatt-admin/titres/{id}",
     *      operationId="getTitresById",
     *      tags={"Titres"},
     *      summary="Récupère un titre par ID",
     *      description="Récupère un titre enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'titre à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Titre récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Titre non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $departement = Titre::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le titre avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 422);
            }
            return $this->successResponse($departement);
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
     *      path="/api/anatt-admin/titres/{id}",
     *      operationId="updateTitres",
     *      tags={"Titres"},
     *      summary="Met à jour un titre existant",
     *      description="Met à jour un titre existant dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'titre à mettre à jour",
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
     *                      description="Nom du titre",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Titre mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Titre non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all", "edit-titles-management"]);
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('titres')->ignore($id)
                ],
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom du titre existe déjà.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), null, 422);
            }

            $titre = Titre::find($id);

            if (!$titre) {
                return $this->errorResponse("Le titre spécifié n'a pas été trouvé.", 'Le titre spécifié n\'a pas été trouvé.', null, 422);
            }

            $Name = $request->input('name');
            $status = $request->input('status');

            if ($this->checkIfExists($Name, $id)) {
                $titre->name = $Name;
                $titre->status = $status;
                $titre->save();
            } else {
                return $this->errorResponse("Un titre portant ce nom existe déjà.", 'Un titre portant ce nom existe déjà.', null, 422);
            }

            return $this->successResponse($titre, 'Titre mis à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du titre.');
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
     *      path="/api/anatt-admin/titres/{id}",
     *      operationId="deleteTitres",
     *      tags={"Titres"},
     *      summary="Supprime un titre",
     *      description="Supprime un titre de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'titre à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Titre supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Titre non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all", "edit-titles-management"]);
        try {
            try {
                $titre = Titre::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le titre avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 422);
            }
            $titre->delete();
            return $this->successResponse($titre, 'Le titre a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    private function titresExist(string $titre_ids)
    {
        $ids = explode(";", $titre_ids);
        // Si tous les titres exists
        return collect($ids)->every(fn ($id) => Titre::whereId(intval($id))->exists());
    }
    /**
     * @OA\Post(
     *      path="/api/anatt-admin/titres/status",
     *      operationId="createTitreStatus",
     *      tags={"Titres"},
     *      summary="Désactivation ou activation d'un titre",
     *      description="Désactivation ou activation d'un titre",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="titre_id",
     *                      description="id du titre",
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
     *          description="le titre n'a pas été trouvé"
     *      )
     * )
     */
    public function status(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-titles-management"]);
        try {
            $validator = Validator::make($request->all(), [
                'titre_id' => 'required',
                'status' => 'required'
            ], [
                'titre_id.required' => 'Aucun titre n\'a été sélectionné',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }

            $titre_id = $request->get('titre_id');
            $status = $request->get('status');
            if (!$this->titresExist($titre_id)) {
                return $this->errorResponse('Vérifiez que le titre sélectionné existe', $validator->errors());
            }

            Titre::where('id', $titre_id)->update(['status' => $status]);
            $titre = Titre::findOrFail($titre_id); // récupérer le titre mis à jour
            return $this->successResponse(['titre' => $titre, 'message' => 'Mise à jour effectué avec succès']);
        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }
}
