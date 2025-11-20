<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatJustifAbsence extends Model
{
    use HasFactory;

    protected $fillable = [
        'motif',
        'fichier_justif',
        'dossier_candidat_id',
        'candidat_justif_absence_id',
        'examen_id',
        'date_soumission',
        'npi',
        'examen_type',
        'dossier_session_id',
        'categorie_permis_id',
        'agent_id',
        'state'

    ];
}
