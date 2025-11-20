<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompoCandidatDeconnexion extends Model
{
    use HasFactory;
    protected $fillable = [
        "candidat_salle_id", "npi", "motif"
    ];
}
