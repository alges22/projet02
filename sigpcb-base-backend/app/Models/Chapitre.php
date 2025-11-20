<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapitre extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name', 'description'];

    public function categoriesPermis()
    {
        return $this->belongsToMany(CategoriePermis::class, 'chapitres_categories_permis', 'chapitre_id', 'categorie_permis_id');
    }
    
}
