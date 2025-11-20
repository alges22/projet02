<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcoursCandidat extends Model
{
    use HasFactory;
    protected $table = 'parcours_candidats';
    protected $connection = "base";
    protected $fillable = [
        'candidat_id',
        'auto_ecole_id',
        'dossier_candidat_id',
        'categorie_permis_id',
        'npi',
        'is_close',
        'examen_id',
        'examen_type',
        'candidat_ecrit_note',
        'candidat_conduite_note',
        'candidat_presence',
        'annexe_anatt_id',
    ];
}
