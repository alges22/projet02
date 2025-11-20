<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\QuestionVague;
use App\Services\Question\RegenerateQuestion;

class QuestionVagueController extends ApiController
{
    /**
     * @OA\Get(
     *      path="/api/anatt-admin/question-vagues/{id}",
     *      operationId="getQuestionVaguesById",
     *      tags={"QuestionVagues"},
     *      summary="Récupère une question-vague par ID",
     *      description="Récupère une question-vague enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la question-vague à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="question-vague récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="question-vague non trouvé"
     *      )
     * )
     */
    public function questions(int $id)
    {
        try {
            /** @var QuestionVague */
            $question_vague = QuestionVague::find($id);

            if (!$question_vague) {
                return $this->errorResponse("Cette vague de questions est introuvable", statuscode: 422);
            }
            $question_ids = explode(";", $question_vague->question_ids);

            // La conversion de $question_ids en un tableau est géré directement par laravel
            $questions = Question::findMany($question_ids);

            $all_question_exists =  $questions->every(fn ($question) => !is_null($question));

            if (!$all_question_exists) {
                return $this->errorResponse("Une erreur s'est produite veuillez regénérer les questions et reprendre", statuscode: 422);
            }

            $question_vague->setAttribute('questions', $questions);
            return $this->successResponse($question_vague);
        } catch (\Throwable $th) {

            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/question-vagues",
     *      operationId="createQuestionVagues",
     *      tags={"QuestionVagues"},
     *      summary="Crée une question-vague",
     *      description="Crée un nouveau question-vagues enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="vague_id",
     *                      description="ID de la vague",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="question_ids",
     *                      description="ID des questions",
     *                      type="integer"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle question-vague créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vague_id' => 'required|integer',
            ]);

            $data = $validator->validated();
            $question_vague = QuestionVague::find($request->vague_id);

            if (!$question_vague) {
                //On se rassure d'enregistrer quelque chose
                $data['question_ids'] = "";
                $question_vague = QuestionVague::create($data);
            }

            $regenerator = new RegenerateQuestion();

            $question_vague->update([
                "question_ids" => implode(";", $regenerator->generate())
            ]);

            return $this->successResponse($question_vague, 'Questions générées pour cette vague avec succès', 201);
        } catch (\Throwable $e) {
            // log the error
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création", null, null, 500);
        }
    }
}
