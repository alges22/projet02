<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Base\Chapitre;
use Illuminate\Support\Facades\DB;
use App\Models\Base\ChapQuestCount;
use App\Http\Controllers\Controller;
use App\Models\QuestionLangue;

class ConfigController extends ApiController
{
    public function setQuestionToCompose(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-base-configuration"]);

        $data = $request->validate([
            "chapitres" => "required|array",
            "chapitres.*" => "integer"
        ]);
        DB::connection('base')->beginTransaction();
        try {
            $total = 0;
            foreach ($data['chapitres'] as $chapitreId => $number) {
                $chapitre = Chapitre::find($chapitreId);
                if (!$chapitre) {
                    return $this->errorResponse("Un des chapitres n'existe pas ou a été déjà retiré, veuillez réessayer.");
                }
                $chapQuest = ChapQuestCount::where([
                    'chapitre_id' => $chapitreId,
                ])->first();

                # Control sur le nombre des questions et chapitre
                $questionIds = $chapitre->questions()->select("id")
                    ->where('status', 'active')
                    ->get()
                    ->pluck('id')
                    ->toArray();

                # Permet de vérifier les questions ayant des audios
                $qCount = QuestionLangue::whereIn('question_id', $questionIds)->count();

                if ($qCount < $number) {
                    $message = "Le chapitre '{$chapitre->name}' ne dispose que de $qCount questions valide(s), mais $number questions ont été demandées.";
                    return $this->errorResponse($message, 422);
                }

                if (!$chapQuest) {
                    ChapQuestCount::create([
                        'chapitre_id' => $chapitreId,
                        'counts' => $number
                    ]);
                } else {
                    $chapQuest->update([
                        'counts' => $number
                    ]);
                }
                $total += $number;
            }

            if ($total > 20) {
                return $this->errorResponse("Le nombre de question dépasse 20. Vous avez renseigné: $total");
            }

            if ($total < 1) {
                return $this->errorResponse("Le nombre de question doit être supérieur à 0");
            }

            DB::connection('base')->commit();
            return $this->successResponse(null, "$total questions ont été configurées avec succès");
        } catch (\Throwable $th) {
            DB::connection('base')->rollBack();
            logger()->error($th);
            return $this->errorResponse($th->getMessage());
        }
    }

    public function index()
    {
        $this->hasAnyPermission(["all", "edit-base-configuration", "read-base-configuration"]);

        $config['questionToCompose'] = ChapQuestCount::all(["chapitre_id", "counts"]);
        return $this->successResponse($config);
    }
}
