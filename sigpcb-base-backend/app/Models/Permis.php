<?php

namespace App\Models;

use App\Models\CategoriePermis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Permis
 *
 * @property int $id
 * @property int $examen_id
 * @property int $dossier_session_id
 * @property string $npi
 * @property int $categorie_permis_id
 * @property int $jury_candidat_id
 * @property int $candidat_salle_id
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $expired_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @package App\Models
 */
class Permis extends Model
{
    use HasFactory;

    protected $table = "permis";
    protected $fillable = [
        'examen_id',
        'dossier_session_id',
        'npi',
        'categorie_permis_id',
        'jury_candidat_id',
        'candidat_salle_id',
        'status',
        'expired_at',
        'code_permis',
        'deliver_id',
        'delivered_at',
        'signataire_id',
        "signed_at"

    ];

    protected $casts = [
        "expired_at" => "datetime",
        'delivered_at' => "datetime",
        'signed_at' => "datetime",
    ];

    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }
}
