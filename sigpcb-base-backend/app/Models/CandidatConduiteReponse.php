<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatConduiteReponse extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'bareme_conduite_id', 'conduite_vague_id','note', 'mention_id','dossier_session_id','jury_candidat_id'];
}
