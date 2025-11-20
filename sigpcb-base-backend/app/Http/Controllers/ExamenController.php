<?php

namespace App\Http\Controllers;

use App\Models\Admin\AnnexeAnatt;
use App\Models\Admin\Examen;
use App\Services\Help;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExamenController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-base/examens",
     *      operationId="getExamsList",
     *      tags={"Examens"},
     *      summary="Récupère la liste des examens",
     *      description="Récupère la liste de tous les examens enregistrés dans la base de données",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des examens récupérée",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'examen",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="date_code",
     *                      description="Date de code de l'examen",
     *                      type="string",
     *                      format="date",
     *                      example="2023-05-01"
     *                  ),
     *                  @OA\Property(
     *                      property="date_conduite",
     *                      description="Date de conduite de l'examen",
     *                      type="string",
     *                      format="date",
     *                      example="2023-05-15"
     *                  ),
     *                  @OA\Property(
     *                      property="date_ouverture",
     *                      description="Date d'ouverture des inscriptions à l'examen",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-01"
     *                  ),
     *                  @OA\Property(
     *                      property="date_cloture",
     *                      description="Date de clôture des inscriptions à l'examen",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *                  @OA\Property(
     *                      property="mois",
     *                      description="Mois de l'examen",
     *                      type="integer",
     *                      example=5
     *                  ),
     *                  @OA\Property(
     *                      property="numero",
     *                      description="Numéro de l'examen",
     *                      type="integer",
     *                      example=1,
     *                      nullable=true
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        try {
            // Récupérer les examens selon les filtres
            $examens = Examen::filter($request->all())->orderByDesc('created_at');

            // Récupérer les examens et enrichir les données
            $agendas = $examens->get()->map(function (Examen $agenda) {
                return $this->mapExamen($agenda);
            });

            // Retourner la réponse
            return $this->successResponse($agendas);

        } catch (\Throwable $th) {
            // Gestion des erreurs
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
     *      path="/api/anatt-base/examens",
     *      operationId="createExam",
     *      tags={"Examens"},
     *      summary="Crée un nouvel examen",
     *      description="Crée un nouvel examen enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="date_code",
     *                  description="Date de l'examen (code)",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-10"
     *              ),
     *              @OA\Property(
     *                  property="date_conduite",
     *                  description="Date de l'examen (conduite)",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-12"
     *              ),
     *              @OA\Property(
     *                  property="date_ouverture",
     *                  description="Date d'ouverture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-03-30"
     *              ),
     *              @OA\Property(
     *                  property="date_cloture",
     *                  description="Date de clôture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-04"
     *              ),
     *              @OA\Property(
     *                  property="mois",
     *                  description="Mois de l'examen (format 'YYYY-MM')",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04"
     *              ),
     *              @OA\Property(
     *                  property="numero",
     *                  description="Numéro de l'examen (facultatif)",
     *                  type="integer",
     *                  example=1
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvel examen créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouvel examen créé",
     *                  type="integer",
     *                   example=2
     *               ),
     *              @OA\Property(
     *                  property="date_code",
     *                  description="Date de l'examen (code)",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-10"
     *              ),
     *              @OA\Property(
     *                  property="date_conduite",
     *                  description="Date de l'examen (conduite)",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-12"
     *              ),
     *              @OA\Property(
     *                  property="date_ouverture",
     *                  description="Date d'ouverture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-03-30"
     *              ),
     *              @OA\Property(
     *                  property="date_cloture",
     *                  description="Date de clôture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-04"
     *              ),
     *              @OA\Property(
     *                  property="mois",
     *                  description="Mois de l'examen (format 'YYYY-MM')",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04"
     *              ),
     *              @OA\Property(
     *                  property="numero",
     *                  description="Numéro de l'examen (facultatif)",
     *                  type="integer",
     *                  example=1
     *              )
     *          )
     *      )
     * )
     */


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "date_ouverture" => 'required|date|before:date_cloture|before:date_code|before:date_conduite',
                "date_cloture" => "required|date|before:date_code|before:date_conduite",
                'date_code' => 'required|date|before:date_conduite|unique:examens,date_code',
                "date_conduite" => "required|date",
                "mois" => "required"
            ], [
                "date_code.required" => "La date de code est obligatoire.",
                'date_code.unique' => 'Un examen a été déjà programmé pour cette date.',
                "date_conduite.required" => "La date de conduite est obligatoire.",
                "date_ouverture.required" => "La date d'ouverture est obligatoire.",
                "date_cloture.required" => "La date de cloture est obligatoire.",
                "mois.required" => "Le champ mois est obligatoire."
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }


            $examen = Examen::create($request->all());
            return $this->successResponse($examen, 'Examen créé avec succès.');
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
     *      path="/api/anatt-base/examens/{id}",
     *      operationId="getExamById",
     *      tags={"Examens"},
     *      summary="Récupère un examen par ID",
     *      description="Récupère les informations d'un examen à partir de son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'examen à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Examen récupéré avec succès",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID de l'examen",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="date_code",
     *                  description="Date du code de l'examen",
     *                  type="string",
     *                  format="date-time",
     *                  example="2022-04-12 14:00:00"
     *              ),
     *              @OA\Property(
     *                  property="date_conduite",
     *                  description="Date de conduite de l'examen",
     *                  type="string",
     *                  format="date-time",
     *                  example="2022-04-15 10:00:00"
     *              ),
     *              @OA\Property(
     *                  property="date_ouverture",
     *                  description="Date d'ouverture de l'examen",
     *                  type="string",
     *                  format="date-time",
     *                  example="2022-04-01 00:00:00"
     *              ),
     *              @OA\Property(
     *                  property="date_cloture",
     *                  description="Date de clôture de l'examen",
     *                  type="string",
     *                  format="date-time",
     *                  example="2022-04-30 23:59:59"
     *              ),
     *              @OA\Property(
     *                  property="mois",
     *                  description="Mois de l'examen",
     *                  type="integer",
     *                  example=4
     *              ),
     *              @OA\Property(
     *                  property="numero",
     *                  description="Numéro de l'examen",
     *                  type="integer",
     *                  example=1234
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Examen non trouvé"
     *      )
     * )
     */

    public function show($id)
    {
        try {
            try {
                $examen = Examen::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'examen avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            $examen = $this->mapExamen($examen);
            return $this->successResponse($examen);
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
     *      path="/api/anatt-base/examens/{id}",
     *      operationId="updateExam",
     *      tags={"Examens"},
     *      summary="Met à jour un examen existant",
     *      description="Met à jour un examen existant enregistré dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'examen à mettre à jour",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="date_code",
     *                  description="Date de l'examen (code)",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-10"
     *              ),
     *              @OA\Property(
     *                  property="date_conduite",
     *                  description="Date de l'examen (conduite)",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-12"
     *              ),
     *              @OA\Property(
     *                  property="date_ouverture",
     *                  description="Date d'ouverture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-03-30"
     *              ),
     *              @OA\Property(
     *                  property="date_cloture",
     *                  description="Date de clôture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-04"
     *              ),
     *              @OA\Property(
     *                  property="mois",
     *                  description="Mois de l'examen (format 'YYYY-MM')",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04"
     *              ),
     *              @OA\Property(
     *                  property="numero",
     *                  description="Numéro de l'examen (facultatif)",
     *                  type="integer",
     *                  example=1
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvel examen créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouvel examen créé",
     *                  type="integer",
     *                   example=2
     *               ),
     *              @OA\Property(
     *                  property="date_code",
     *                  description="Date de l'examen (code)",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-10"
     *              ),
     *              @OA\Property(
     *                  property="date_conduite",
     *                  description="Date de l'examen (conduite)",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-12"
     *              ),
     *              @OA\Property(
     *                  property="date_ouverture",
     *                  description="Date d'ouverture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-03-30"
     *              ),
     *              @OA\Property(
     *                  property="date_cloture",
     *                  description="Date de clôture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04-04"
     *              ),
     *              @OA\Property(
     *                  property="mois",
     *                  description="Mois de l'examen (format 'YYYY-MM')",
     *                  type="string",
     *                  format="date",
     *                  example="2023-04"
     *              ),
     *              @OA\Property(
     *                  property="numero",
     *                  description="Numéro de l'examen (facultatif)",
     *                  type="integer",
     *                  example=1
     *              )
     *          )
     *      ),
     *       @OA\Response(
     *          response=404,
     *          description="Examen non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                "date_ouverture" => 'required|date|before:date_cloture|before:date_code|before:date_conduite',
                "date_cloture" => "required|date|before:date_code|before:date_conduite",
                'date_code' => [
                    'required',
                    'date',
                    'before:date_conduite',
                    Rule::unique('examens')->ignore($id)
                ],
                "date_conduite" => "required|date",
                "mois" => "required"
            ], [
                "date_code.required" => "La date de code est obligatoire.",
                'date_code.unique' => 'Un examen a été déjà programmé pour cette date.',
                "date_conduite.required" => "La date de conduite est obligatoire.",
                "date_ouverture.required" => "La date d'ouverture est obligatoire.",
                "date_cloture.required" => "La date de cloture est obligatoire.",
                "mois.required" => "Le champ mois est obligatoire."
            ]);


            if ($validator->fails()) {
                return $this->errorResponse('La validation a échoué.', $validator->errors());
            }

            try {
                $examen = Examen::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'examen avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            $examen->update($request->all());
            return $this->successResponse($examen, 'Examen mis à jour avec succès.');
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
     *      path="/api/anatt-base/examens/{id}",
     *      operationId="deleteExam",
     *      tags={"Examens"},
     *      summary="Supprime un examen existant",
     *      description="Supprime un examen existant de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'examen à supprimer",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              example=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Examen supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Examen non trouvé"
     *      )
     * )
     */

    public function destroy($id)
    {
        try {
            try {
                $examen = Examen::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'examen avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            $examen->delete();
            return $this->successResponse($examen, 'L\'examen a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function currentExamen()
    {
        $examen = Examen::recent();

        if (!$examen) {
            return $this->successResponse(null, "Aucune session n'est en cours actuellement et aucune session proche n'est trouvée");
        }

        $examen = $this->mapExamen($examen);

        return $this->successResponse($examen);
    }

    private function mapExamen(Examen  $examen)
    {
        return $examen->asAgenda();
    }
}
