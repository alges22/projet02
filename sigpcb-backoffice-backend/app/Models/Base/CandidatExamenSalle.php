<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $salle_compo_id
 * @property int $annexe_id
 * @property int $examen_id
 * @property int $categorie_permis_id
 *
 */
class CandidatExamenSalle extends Model
{
    use HasFactory;

    protected $connection  = "base";
}
