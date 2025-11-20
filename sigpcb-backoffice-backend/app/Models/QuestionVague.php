<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class QuestionVague
 * @package App\Models
 *
 * @property int $id
 * @property int $vague_id
 * @property string $question_ids
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class QuestionVague extends Model
{
    use HasFactory;

    protected $fillable = ['vague_id', 'question_ids'];
}
