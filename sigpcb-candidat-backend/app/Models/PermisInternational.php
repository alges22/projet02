<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisInternational extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $fillable = [
        'email',
        'num_permis',
        'npi',
        'num_permis',
        'state',
        'permis_file',
        'date_validation',
        'date_rejet',
        'categorie_permis_ids',
    ];
}
