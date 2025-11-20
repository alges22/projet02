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
class Question extends Model
{
    use HasFactory;

    protected $connection = "base";
    protected $fillable = ['texte', 'name', 'chapitre_id', 'illustration', 'code_illustration', 'illustration_type','status'];

    protected $casts = [
        'categorie_permis_id' => 'integer',
    ];


    public function reponses()
    {
        return $this->hasMany(QuestionReponse::class);
    }

    public function questionlangue()
    {
        return $this->hasMany(QuestionLangue::class);
    }
    public function audiolangues()
    {
        return $this->hasMany(QuestionLangue::class);
    }
}
