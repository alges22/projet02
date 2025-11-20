<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldAutoEcole extends Model
{
    use HasFactory;

    protected $fillable = [
        "name", 'departement', 'commune', 'moniteur_npis', 'promoteur_npi', 'adresse', 'agrement', 'expire_licence', 'code_licence', 'email_pro', 'email_promoteur', 'telephone_pro', 'ifu', 'vehicules'
    ];
}
