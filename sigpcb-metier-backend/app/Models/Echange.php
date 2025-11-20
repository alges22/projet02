<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echange extends Model
{
    use HasFactory;
    protected $fillable = [
        'email',
        'num_permis',
        'permis_file',
        'npi',
        'delivrance_ville',
        'group_sanguin',
        'state',
        'categorie_permis_ids',
        'delivrance_date',
        'structure_email',
        'group_sanguin_file',
        'date_validation',
        'date_rejet',
    ];
}
