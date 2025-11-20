<?php

namespace App\Models\Admin;

use App\Models\Examen as ModelsExamen;
use Carbon\Carbon;
use App\Services\Help;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string|string $code_state (init, pending, closed)
 * @property Carbon $date_code (init, pending, closed)
 * @property string $debut_etude_dossier_at
 * @property string $fin_etude_dossier_at
 * @property string $date_conduite
 * @property string $conduite_state
 * @property string $debut_gestion_rejet_at
 * @property string $date_convocation
 * @property string $convocation_state
 * @property string $fin_gestion_rejet_at
 * @property bool $opened
 * @property int $id
 *
 */
class Examen extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $fillable = ['id', 'debut_etude_dossier_at', 'fin_etude_dossier_at', 'debut_gestion_rejet_at', 'fin_gestion_rejet_at', 'date_code', 'annee', 'numero', 'date_conduite', 'date_convocation', 'status', 'mois', 'closed', 'session_long', 'name', 'type'];

    protected $casts = [
        "debut_etude_dossier_at" => "datetime",
        "fin_etude_dossier_at" => "datetime",
        "debut_gestion_rejet_at" => "datetime",
        "fin_gestion_rejet_at" => "datetime",
        "date_code" => "datetime",
        "date_conduite" => "datetime",
        "date_convocation" => "datetime"
    ];
    protected $table = "examens";
}
