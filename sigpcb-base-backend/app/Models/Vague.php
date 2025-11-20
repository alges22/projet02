<?php

namespace App\Models;

use App\Models\Admin\Examen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Summary of Vague
 * @property int $examen_id
 * @property int $annexe_anatt_id
 * @property int $id
 * @property string $status
 * @property int $numero
 * @property Carbon $date_compo
 * @property array|string|null $questions
 */
class Vague extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'examen_id',
        'annexe_anatt_id',
        'date_compo',
        'questions',
        'closed_at',
        'salle_compo_id',
        'status',
        'categorie_permis_id',
    ];

    const STATUES = ["pending", "new", "paused", "closed"];
    protected $casts = [
        "date_compo" => "datetime",
        "closed_at" => "datetime",
        'questions' => "array"
    ];


    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }



    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function isStatus(string $status)
    {
        return $this->status === $status;
    }

    public function isNotStatus(string $status)
    {
        return $this->status === $status;
    }

    public static function current($salle_compo_id, $examen_id): Vague | null
    {
        return  Vague::where([
            "salle_compo_id" => $salle_compo_id,
            "examen_id" => $examen_id,
        ])->has('candidats')->where('status', '!=', 'closed')->orderBy('numero')->first();
    }

    public function candidats()
    {
        return $this->hasMany(CandidatExamenSalle::class);
    }
}
