<?php

namespace App\Models;

use App\Models\Examinateur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExaminateurCategoriePermis extends Model
{
    use HasFactory;
    protected $fillable = [
        'examinateur_id',
        'categorie_permis_id',
    ];
    public function examinateur()
    {
        return $this->belongsTo(Examinateur::class);
    }
}
