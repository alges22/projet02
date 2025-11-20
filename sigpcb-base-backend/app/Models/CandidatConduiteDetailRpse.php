<?php

namespace App\Models;

use App\Models\Admin\Examen;
use App\Models\Candidat\DossierSession;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatConduiteDetailRpse extends Model
{
    use HasFactory;

    protected $fillable = [
        'bareme_conduite_id',
        'conduite_vague_id',
        'sub_bareme_id',
        'dossier_session_id',
        'jury_candidat_id',
        'note',
    ];
    // Relation avec le modèle BaremeConduite
    public function baremeConduite()
    {
        return $this->belongsTo(BaremeConduite::class);
    }

    // Relation avec le modèle ConduiteVague
    public function conduiteVague()
    {
        return $this->belongsTo(ConduiteVague::class);
    }

    // Relation avec le modèle SubBareme
    public function subBareme()
    {
        return $this->belongsTo(SubBareme::class);
    }

    // Relation avec le modèle DossierSession
    public function dossierSession()
    {
        return $this->belongsTo(DossierSession::class);
    }

    // Relation avec le modèle JuryCandidat
    public function juryCandidat()
    {
        return $this->belongsTo(JuryCandidat::class);
    }
}
