<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapitreCategoriePermis extends Model
{
    use HasFactory;

    protected $table = "chapitres_categories_permis";

    protected $fillable = ['chapitre_id', 'categorie_permis_id'];
}