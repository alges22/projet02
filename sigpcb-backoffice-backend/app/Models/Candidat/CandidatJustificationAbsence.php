<?php

namespace App\Models\Candidat;

use App\Models\Examen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatJustificationAbsence extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $table = "candidat_justification_absences";
    protected $guarded = [];

    // Définir la relation avec Examen
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }
    // Définir la relation avec DossierSession
    public function dossierSession()
    {
        return $this->belongsTo(DossierSession::class);
    }
    public function validator()
    {
        return $this->belongsTo(User::class,'validator_id');
    }
}
