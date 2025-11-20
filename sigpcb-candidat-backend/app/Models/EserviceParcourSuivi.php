<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EserviceParcourSuivi extends Model
{
    use HasFactory;
    protected $table = 'eservice_parcour_suivis';
    protected $connection = "base";
    protected $fillable = [
        'candidat_id',
        'auto_ecole_id',
        'agent_id',
        'dossier_candidat_id',
        'dossier_session_id',
        'categorie_permis_id',
        'npi',
        'slug',
        'message',
        'bouton_slug',
        'eservice',
        'action',
        'url',
        'date_action',
        'service',
    ];
}
