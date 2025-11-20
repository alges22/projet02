<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatJustifAbsence extends Model
{
    use HasFactory;
    protected  $table = "candidat_justif_absences";

    protected $guarded = [];
}
