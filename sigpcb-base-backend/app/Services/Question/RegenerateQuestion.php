<?php

namespace App\Services\Question;

use App\Models\Question;

/**
 * Cette classe a pour spécialité de générer  20 questionId, pour des questions existantes
 */
class RegenerateQuestion
{
    protected $count = 20;

    protected $questionIds = [];

    private function hasNotId(int $id)
    {
        return !in_array($id, $this->questionIds);
    }

    public function generate()
    {
        // On commence la génération de questionId

        for ($i = 1; $i <= $this->getCount(); $i++) {

            $questionId = $this->randQuestionId();

            $canInsert = $this->canInsert($questionId);

            if (!$canInsert) {
                /**
                 * On regénère un autre ID si on ne peut pas utliser le précédent
                 */
                while (!$canInsert) {

                    $questionId = $this->randQuestionId();

                    // A un moment donnée ceci deviendra true, et la boucle while va s'arrêter
                    $canInsert = $this->canInsert($questionId);
                }
            }
            $this->questionIds[] = $questionId;
        }

        return $this->questionIds;
    }

    /**
     * Le nombre maximal de question existante
     * @return int
     */
    private function limit()
    {
        return Question::count();
    }

    /**
     * Si la question existe
     *
     * @param integer $id
     * @return bool
     */
    private function questionExists(int $id)
    {
        return Question::where('id', $id)->exists();
    }

    private function randQuestionId()
    {
        // On génère un identifiant au harsard
        return rand(1, $this->limit());
    }

    /**
     * Vérifie l'unicité et l'existence de la question
     *
     * @param integer $id
     * @return boolean
     */
    private function canInsert(int $id)
    {
        return $this->hasNotId($id) && $this->questionExists($id);
    }

    private function getCount()
    {
        /**
         * Ceci est important
         * Si le nombre de count à prendre dé passe aux nombres de questions existantes,
         * On prend juste le nombre total de question
         */
        return $this->count > $this->limit() ? $this->limit() : $this->count;
    }
}