<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Question
 * @package App\Models
 *
 * @property int $id
 * @property int $langue_id
 * @property int $categorie_permis_id
 * @property string|null $audio
 * @property string|null $illustration
 * @property int $time
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 */
class QuestionLangue extends Model
{
    use HasFactory;

    protected $connection = "base";
    protected $fillable = ['question_id', 'langue_id', 'audio', 'time'];

    protected $casts = [
        'time' => 'integer',
    ];


    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
