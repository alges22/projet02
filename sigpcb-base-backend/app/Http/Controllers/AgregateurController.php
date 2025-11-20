<?php

namespace App\Http\Controllers;

use App\Models\Agregateur;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AgregateurController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/agregateurs",
     *     operationId="getAllAgregateurs",
     *     tags={"Agregateurs"},
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
            $agregateurs = Agregateur::orderBy('id','desc')->get();
            return $this->successResponse($agregateurs);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function checkIfExists($name) {
        $existing = Agregateur::whereRaw('LOWER(name) LIKE ?', [strtolower($name)])->first();
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
     *      path="/api/anatt-base/agregateurs",
     *      operationId="createAgregateurs",
     *      tags={"Agregateurs"},
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
                'name' => [
                    'required',
                    'unique:agregateurs,name'
                ],
                'photo' => 'required|file|mimes:jpeg,png,jpg|max:2048',
                'status' => 'required|boolean',
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de l\'agrégateur existe déjà.',
                'photo.required' => 'Le champ photo est obligatoire.',
                'status.required' => 'Le champ status est obligatoire.',
                'photo.file' => 'Le champ photo doit être un fichier.',
                'photo.mimes' => 'Le champ photo doit être un fichier jpeg, png ou jpg.',
                'photo.max' => 'Le champ photo doit être inférieur à 2 Mo.',
                'status.boolean' => 'Le champ statut doit être un booléen.',
            ]);

            $input = $request->all();
            if ($request->hasFile('photo')) {
                $imagePath = $request->file('photo')->store('photo', 'public');
                $input['photo'] = $imagePath;
            }

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }
            $name = $request->input('name');
            $status = $request->input('status');
            $photo = $input['photo'];
            if ($this->checkIfExists($name)) {
                $new = new Agregateur();
                $new->name = $name;
                $new->photo = $photo;
                $new->status = $status;
                $new->save();
            return $this->successResponse($new, 'Agrégateur créé avec succès.', 200);

            } else {
            return $this->errorResponse('Un agregateur portant ce nom existe déjà.');

            }
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
     *      path="/api/anatt-base/agregateurs/{id}",
     *      operationId="getAgregateursById",
     *      tags={"Agregateurs"},
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
            try {
                $agregateur = Agregateur::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'agrégateur avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($agregateur);
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
     *      path="/api/anatt-base/agregateurs/{id}",
     *      operationId="updateAgregateurs",
     *      tags={"Agregateurs"},
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
                    Rule::unique('agregateurs')->ignore(intval($id)),
                ],
                'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
                'status' => 'required|boolean',
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de l\'agrégateur existe déjà.',
                'photo.file' => 'Le fichier doit être un fichier image.',
                'photo.mimes' => 'Le fichier doit être jpeg, png ou jpg.',
                'photo.max' => 'Le fichier doit être inférieur à 2 Mo.',
                'status.required' => 'Le champ statut est obligatoire.',
                'status.boolean' => 'Le champ statut doit être un booléen.',

            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            try {
                $agregateur = Agregateur::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'agrégateur avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }

            $input = $request->all();
            if ($request->hasFile('photo')) {
                $agregateur = Agregateur::findOrFail($id);
            
                // Supprime le fichier existant avant de le remplacer
                if ($agregateur->photo) {
                    $existingFilePath = 'public/photo/' . $agregateur->photo;
                    Storage::delete($existingFilePath);
                }
                $file = $request->file('photo');
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $imagePath = $file->storeAs('public/photo', $fileName);
                $input['photo'] = str_replace('public/', '', $imagePath);
            }            
            $agregateur->update($input);
            return $this->successResponse($agregateur, 'Agrégateur mis à jour avec succès.');
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
     *      path="/api/anatt-base/agregateurs/{id}",
     *      operationId="deleteAgregateurs",
     *      tags={"Agregateurs"},
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
            try {
                $agregateur = Agregateur::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'agrégateur avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $agregateur->delete();
            return $this->successResponse($agregateur, 'L\'agrégateur a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    private function agregateursExist(string $agregateur_ids)
    {
        $ids = explode(";", $agregateur_ids);
        // Si tous les users exists
        return collect($ids)->every(fn ($id) => Agregateur::whereId(intval($id))->exists());
    }

        /**
     * @OA\Post(
     *      path="/api/anatt-admin/agregateurs/status",
     *      operationId="createAgregateursStatus",
     *      tags={"Agregateurs"},
     *      summary="Désactivation ou activation d'un agregateur",
     *      description="Désactivation ou activation d'un agregateur",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="agregateur_id",
     *                      description="id de l'agregateur",
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
     *          description="l'agregateur n'a pas été trouvé"
     *      )
     * )
     */
    public function status(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'agregateur_id' => 'required',
                'status' => 'required'
            ], [
                'agregateur_id.required' => 'Aucun agregateur n\'a été sélectionné',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);
    
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), 422);
            }
    
            $agregateur_id = $request->get('agregateur_id');
            $status = $request->get('status');
            if (!$this->agregateursExist($agregateur_id)) {
                return $this->errorResponse('Vérifiez que l\'agregateur sélectionné existe', $validator->errors());
            }
    
            $agregateur = Agregateur::where('id', $agregateur_id)->first();
    
            Agregateur::where('id', $agregateur_id)->update(['status' => $status]);
            $agregateur = Agregateur::findOrFail($agregateur_id); // récupérer la langue mis à jour
            return $this->successResponse(['agregateur' => $agregateur, 'message' => 'Mise à jour effectué avec succès']);
        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }
}
