<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Reponse;
use App\Models\Restriction;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Faq;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FaqController extends ApiController
{
        /**
     * @OA\Get(
     *     path="/api/anatt-admin/faqs",
     *     operationId="getAllFaqs",
     *     tags={"Faqs"},
     *     summary="Récupérer la liste des Faqs",
     *     description="Récupère une liste des Faqs enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des Faqs récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du faq",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="question",
     *                      description="le libellé de la question",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="response",
     *                      description="la réponse de la question",
     *                      type="string"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $this->hasAnyPermission(["all", "read-faq-management","edit-faq-management"]);

        try {
            $query = Faq::orderByDesc('id');

            // Appliquer les filtres
            $query = $this->applyFilters($query);

            $faqs = $query->paginate(10);

            return $this->successResponse($faqs);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
    public function applyFilters($query)
    {
        // Filtre par état (state)
        if ($type = request('type')) {
            $query->where('type', $type);
        }
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('question', 'LIKE', "%$search%");
        }
        return $query;
    }


    public function checkIfExists($Name) {
        $existing = Faq::whereRaw('LOWER(question) LIKE ?', [strtolower($Name)])->first();
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
     *      path="/api/anatt-admin/faqs",
     *      operationId="createFaqs",
     *      tags={"Faqs"},
     *      summary="Crée une nouvelle foire aux questions",
     *      description="Crée une nouvelle foire aux questions enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="question",
     *                      description="le libellé de la question",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="response",
     *                      description="la réponse de la question",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle foire aux questions créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $this->hasAnyPermission(["all","edit-faq-management"]);

            $validator = Validator::make($request->all(), [
                'question' => [
                    'required',
                    'unique:faqs,question'
                ],
                'response' => [
                    'required',
                ],
                'type'=> 'required|in:autoecole,candidat'
            ], [
                'response.required' => 'Le champ réponse est obligatoire.',
                'question.required' => 'Le champ question est obligatoire.',
                'question.unique' => 'Cette question existe déjà.',
                'type.required' => "Vous devez indiquer le type d'utilisateur (auto-école ou candidat).",
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), null, 422);
            }
            $Name = $request->input('question');
            $response = $request->input('response');
            $type = $request->input('type');
            if ($this->checkIfExists($Name)) {
                $faq = new Faq();
                $faq->question = $Name;
                $faq->response = $response;
                $faq->type = $type;
                $faq->save();
            } else {
            return $this->errorResponse("Cette faq existe déjà.",'Cette faq existe déjà.',null, 422);

            }
            return $this->successResponse($faq, 'Faq créé avec succès.');
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
     *      path="/api/anatt-admin/faqs/{id}",
     *      operationId="getFaqsById",
     *      tags={"Faqs"},
     *      summary="Récupère une faq par ID",
     *      description="Récupère une faq enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la faq à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="faq récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="faq non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {

            try {
                $faq = Faq::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La faq avec n\'a pas été trouvé.', [], null, 422);
            }
            return $this->successResponse($faq);
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
     *      path="/api/anatt-admin/faq/{id}",
     *      operationId="updateFaqs",
     *      tags={"Faqs"},
     *      summary="Met à jour une faq existante",
     *      description="Met à jour une faq existante dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la faq à mettre à jour",
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
     *                      property="question",
     *                      description="le libellé de la question",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="response",
     *                      description="la réponse de la question",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="faq mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="faq non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $this->hasAnyPermission(["all","edit-faq-management"]);

            $validator = Validator::make($request->all(), [
                'question' => [
                    'required',
                ],
                'response' => 'required',
                'type'=> 'required|in:autoecole,candidat'

            ], [
                'response.required' => 'La réponse est obligatoire.',
                'question.required' => 'La question est obligatoire.',
                'type.required' => "Le type est obligatoire.",
                'type.in' => "Le type doit être autoecole ou candidat."
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), null, 422);
            }

            $faq = Faq::find($id);

            if (!$faq) {
                return $this->errorResponse("La faq spécifiée n'a pas été trouvé.", 'La faq spécifiée n\'a pas été trouvé.', null, 422);
            }

            $Name = $request->input('question');
            $reponse = $request->input('response');
            $type = $request->input('type');

            $faq->question = $Name;
            $faq->response = $reponse;
            $faq->type = $type;
            $faq->save();


            return $this->successResponse($faq, 'Faq mis à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour de la faq.',500);
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
     *      path="/api/anatt-admin/faqs/{id}",
     *      operationId="deleteFaqs",
     *      tags={"Faqs"},
     *      summary="Supprime une faq",
     *      description="Supprime une faq de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la faq à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="faq supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="faq non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->hasAnyPermission(["all","edit-faq-management"]);

            try {
                $faq = Faq::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Cette faq n\'a pas été trouvé.', [], null, 422);
            }
            $faq->delete();
            return $this->successResponse($faq, 'La faq a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    private function titresExist(string $faq_ids)
    {
        $ids = explode(";", $faq_ids);
        // Si tous les titres exists
        return collect($ids)->every(fn ($id) => Faq::whereId(intval($id))->exists());
    }
}
