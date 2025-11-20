<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminateurCategoriePermis extends Model
{
    use HasFactory;
    protected $connection  = "admin";
    protected $table = "examinateur_categorie_permis";
}
