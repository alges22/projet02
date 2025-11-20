<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class QuestionReponse
 * @package App\Models
 *
 * @property int $id
 * @property int $question_id
 * @property int $reponse_id
 * @property bool $is_correct
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class QuestionReponse extends Model
{
    use HasFactory;

    protected $connection = "base";
    protected $fillable = ['question_id', 'reponse_id', 'is_correct'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function reponse()
    {
        return $this->belongsTo(Reponse::class);
    }
}
