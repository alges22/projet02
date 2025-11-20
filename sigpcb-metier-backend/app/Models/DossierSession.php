<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierSession extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function dossierCandidat()
    {
        return $this->belongsTo(DossierCandidat::class);
    }

    public function justifAbsences()
    {
        return $this->hasOne(CandidatJustifAbsence::class);
    }
}
