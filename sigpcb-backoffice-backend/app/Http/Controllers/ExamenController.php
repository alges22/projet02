<?php

namespace App\Http\Controllers;

use App\Models\Examen;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DateService;
use Illuminate\Support\Carbon;
use App\Http\Controllers\ApiController;
use App\Models\AnnexeAnatt;
use App\Models\Candidat\DossierSession;
use App\Models\Examinateur;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExamenController extends ApiController
{

    public function __construct(private DateService $dateService) {}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-admin/examens",
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
     *                      property="date_etude_dossier",
     *                      description="Date d'ouverture des inscriptions à l'examen",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-01"
     *                  ),
     *                  @OA\Property(
     *                      property="date_gestion_rejet",
     *                      description="Date de gestion des dossiers rejeté",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *                  @OA\Property(
     *                      property="date_convocation",
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
        $this->hasAnyPermission(["all", "read-agenda-management","edit-agenda-management"]);

        try {
            $examens = $this->importFromBase("examens", $request->all());
            return $this->successResponse($examens);
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
     *      path="/api/anatt-admin/examens",
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
     *                  property="date_etude_dossier",
     *                  description="Date d'ouverture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-03-30"
     *              ),
     *                  @OA\Property(
     *                      property="date_gestion_rejet",
     *                      description="Date de gestion des dossiers rejeté",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *              @OA\Property(
     *                  property="date_convocation",
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
     *                  property="date_etude_dossier",
     *                  description="Date d'ouverture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-03-30"
     *              ),
     *                  @OA\Property(
     *                      property="date_gestion_rejet",
     *                      description="Date de gestion des dossiers rejeté",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *              @OA\Property(
     *                  property="date_convocation",
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
        $this->hasAnyPermission(["all","edit-agenda-management"]);

        try {
            $rules = [
                "debut_etude_dossier_at" => 'required|date',
                "fin_etude_dossier_at" => 'required|date',
                "debut_gestion_rejet_at" => 'required|date',
                "fin_gestion_rejet_at" => 'required|date',
                "date_convocation" => "required|date",
                'date_code' => 'required|date',
                "type" => "required|in:extra,ordinaire,militaire",
                "annexe_ids" => "required|array|min:1",
                "annexe_ids.*" => "required|integer",
            ];

            $validator = Validator::make($request->all(), $rules, [
                "date_code.required" => "La date de composition du code est obligatoire.",
                'date_code.unique' => 'Un examen a été déjà programmé pour cette date.',
                "date_conduite.required" => "La date de conduite est obligatoire.",
                "debut_gestion_rejet_at.required" => "La date début de traitement des rejets est obligatoire.",
                "fin_gestion_rejet_at.required" => "La date fin de traitement des rejets est obligatoire.",
                "debut_etude_dossier_at.required" => "La date début d'étude des dossiers est obligatoire.",
                "fin_etude_dossier_at.required" => "La date fin d'étude des dossiers est obligatoire.",
                "date_convocation.required" => "La date de cloture est obligatoire.",
                "name.required" => "Le nom de la session est obligatoire.",
                "type.required" => "Le type de la session est obligatoire.",
                "annexe_ids.array" => "Les annexes doivent être un tableau.",
                "annexe_ids.*.integer" => "Chaque annexe doit être un identifiant entier valide."
            ]);

            if ($request->has('annexe_ids')) {
                $annexeIds = $request->annexe_ids;
                foreach ($annexeIds as $annexeId) {
                    if (!AnnexeAnatt::where('id', $annexeId)->exists()) {
                        $validator->after(function ($validator) use ($annexeId) {
                            $validator->errors()->add('annexe_ids', "L'annexe avec l'ID '$annexeId' n'existe pas.");
                        });
                    }
                }
            }
            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), null, 422);
            }

            $dateCarbon = Carbon::parse($request->date_code);
            $data = $request->all();
            $data['mois'] = $dateCarbon->monthName;
            $data['annee'] = $dateCarbon->year;
            $data['name'] = $request->name;


            if (isset($request->annexe_ids)) {
                $data['annexe_ids'] = $request->annexe_ids;
            }

            $examen = Examen::create($data);

            $this->makeLongSession($request);

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
     *      path="/api/anatt-admin/examens/{id}",
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
     *                  property="date_etude_dossier",
     *                  description="Date d'ouverture de l'examen",
     *                  type="string",
     *                  format="date-time",
     *                  example="2022-04-01 00:00:00"
     *              ),
     *                  @OA\Property(
     *                      property="date_gestion_rejet",
     *                      description="Date de gestion des dossiers rejeté",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *              @OA\Property(
     *                  property="date_convocation",
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
                return $this->errorResponse('Cet examen n\'a pas été trouvé.', [], null, 404);
            }
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
     *      path="/api/anatt-admin/examens/{id}",
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
     *                  property="date_etude_dossier",
     *                  description="Date d'ouverture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-03-30"
     *              ),
     *                  @OA\Property(
     *                      property="date_gestion_rejet",
     *                      description="Date de gestion des dossiers rejeté",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *              @OA\Property(
     *                  property="date_convocation",
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
     *                  property="date_etude_dossier",
     *                  description="Date d'ouverture des inscriptions",
     *                  type="string",
     *                  format="date",
     *                  example="2023-03-30"
     *              ),
     *                  @OA\Property(
     *                      property="date_gestion_rejet",
     *                      description="Date de gestion des dossiers rejeté",
     *                      type="string",
     *                      format="date",
     *                      example="2023-03-31"
     *                  ),
     *              @OA\Property(
     *                  property="date_convocation",
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
        $this->hasAnyPermission(["all","edit-agenda-management"]);

        try {

            $rules = [
                "debut_etude_dossier_at" => 'required|date',
                "fin_etude_dossier_at" => 'required|date',
                "debut_gestion_rejet_at" => 'required|date',
                "fin_gestion_rejet_at" => 'required|date',
                "date_convocation" => "required|date",
                'date_code' => 'required|date',
                "date_conduite" => "required|date",
                "type" => "required|in:extra,ordinaire,militaire",
                "annexe_ids" => "required|array|min:1",
                "annexe_ids.*" => "required|integer",
            ];

            $validator = Validator::make($request->all(), $rules, [
                "date_code.required" => "La date de composition du code est obligatoire.",
                'date_code.unique' => 'Un examen a été déjà programmé pour cette date.',
                "date_conduite.required" => "La date de conduite est obligatoire.",
                "debut_gestion_rejet_at.required" => "La date début de traitement des rejets est obligatoire.",
                "fin_gestion_rejet_at.required" => "La date fin de traitement des rejets est obligatoire.",
                "debut_etude_dossier_at.required" => "La date début d'étude des dossiers est obligatoire.",
                "fin_etude_dossier_at.required" => "La date fin d'étude des dossiers est obligatoire.",
                "date_convocation.required" => "La date de cloture est obligatoire.",
                "type.required" => "Le type de la session est obligatoire.",
                "annexe_ids.array" => "Les annexes doivent être un tableau.",
                "annexe_ids.*.integer" => "Chaque annexe doit être un identifiant entier valide."
            ]);
            if ($request->has('annexe_ids')) {
                $annexeIds = $request->annexe_ids;
                foreach ($annexeIds as $annexeId) {
                    if (!AnnexeAnatt::where('id', $annexeId)->exists()) {
                        $validator->after(function ($validator) use ($annexeId) {
                            $validator->errors()->add('annexe_ids', "L'annexe avec l'ID '$annexeId' n'existe pas.");
                        });
                    }
                }
            }
            if ($validator->fails()) {
                return $this->errorResponse('La validation a échoué.', $validator->errors(), null, 422);
            }

            $dateCarbon = Carbon::parse($request->date_code);
            $data = $request->all();
            $data['mois'] = $dateCarbon->monthName;
            $data['annee'] = $dateCarbon->year;
            $data['name'] = $request->name;

            $dossierSession = DossierSession::where('examen_id', $id)
                ->where('closed', false)
                ->where('abandoned', false)
                ->first();

            if ($dossierSession) {
                return $this->errorResponse('Cet examen ne peut plus être modifié car il est déjà utilisé.', [], null, 422);
            }
            try {
                $examen = Examen::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Cet Examen n\'a pas été trouvé.', [], null, 422);
            }
            if (isset($request->annexe_ids)) {
                $data['annexe_ids'] = $request->annexe_ids;
            }

            $examen->update($data);
            $this->makeLongSession($request);
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
     *      path="/api/anatt-admin/examens/{id}",
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
        $this->hasAnyPermission(["all","edit-agenda-management"]);

        try {
            try {
                $examen = Examen::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Cet examen n\'a pas été trouvé.', [], null, 422);
            }
            $examen->delete();
            return $this->successResponse($examen, 'L\'examen a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function recentExamen()
    {
        $mois = request('mois');
        if (request()->has('mois') && !$this->dateService->hasFullMonth($mois)) {
            return $this->errorResponse("Le mois est incorrect ou mal orthographié: Résultat non trouvé", 404);
        }
        $mois = $this->dateService->getFullMonthNow();

        $annee = date('Y');
        // Récupérer l'examens actuel le plus proche
        $examen = Examen::where('annee', $annee)
            ->orderBy('date_code', 'desc')
            ->get()->filter(function ($examen) use ($mois) {
                return strtolower($examen->mois) == strtolower($mois);
            })->first();

        $data = [
            "recent" => $examen,
            "near" => null
        ];
        return $this->successResponse($data);
    }

    public function sessionEnCours()
    {
        return $this->exportFromBase("examens/session-en-cours", request()->all());
    }

    private function makeLongSession(Request $request)
    {
        $examens = Examen::filter([
            'date_code' => Carbon::parse($request->date_code)->format('Y-m-d'),
            'type' => $request->type
        ])->orderBy('date_code')->get();

        foreach ($examens as $key => $value) {
            $numero = $key + 1;
            $sessionInstance = Carbon::parse($value->date_code);
            $mois = ucfirst($sessionInstance->monthName);
            $annee = $sessionInstance->year;
            $days = $sessionInstance->day;
            $date = Str::ucfirst(sprintf("%s %s %s", $days, $mois, $annee));
            $type = Str::upper($value->type);

            $longName = sprintf(
                "{$value->name} {$date}/N°{$numero} S/{$type}"
            );
            $value->update([
                'numero' => $numero,
                'session_long' => trim($longName)
            ]);
        }
    }

    public function allSession(Request $request)
    {
        try {
            // Récupérer l'ID de l'utilisateur connecté
            $user = auth()->user();

            // Récupérer l'examinateur lié à cet utilisateur
            $examinateur = Examinateur::where('user_id', $user->id)->first();

            if (!$examinateur) {
                return $this->errorResponse('Cet utilisateur n\'est pas un examinateur.', 404);
            }

            // Récupérer l'ID de l'annexe de l'examinateur
            $annexeId = $examinateur->annexe_anatt_id;

            // Récupérer les examens qui ne sont pas fermés (closed => false) et dont l'annexe_id de l'examinateur est dans le tableau annexe_ids
            $examens = Examen::where('closed', false)
                ->whereJsonContains('annexe_ids', $annexeId) // Vérifie si l'annexe_id est présent dans le tableau annexe_ids
                ->orderByDesc('id')
                ->get();

            return $this->successResponse($examens);
        } catch (\Throwable $th) {
            // Log l'erreur pour le debugging
            logger()->error($th);

            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!', 500);
        }
    }
}
