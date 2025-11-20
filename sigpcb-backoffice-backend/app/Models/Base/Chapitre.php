<?php

namespace App\Models\Base;

use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chapitre extends Model
{
    use HasFactory;
    protected $connection = "base";
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    public function categoriesPermis()
    {
        return $this->belongsToMany(CategoriePermis::class, 'chapitres_categories_permis', 'chapitre_id', 'categorie_permis_id');
    }
}
