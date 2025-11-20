<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SalleCompoVague
 *
 * @property int $id
 * @property int $categorie_permis_id
 * @property int $langue_id
 * @property int $examen_id
 * @property array $npis
 * @property int $salle_compo_id
 * @property int $vague_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @package App\Models
 */
class SalleCompoVague extends Model
{
    protected $fillable = [
        'categorie_permis_id',
        'langue_id',
        'examen_id',
        'npis',
        'salle_compo_id',
        'vague_id',
    ];

    protected $table = "salle_compo_vague";
}
