<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriePermisExtensible extends Model
{
    protected $table = 'categorie_permis_extensibles';

    protected $fillable = [
        'categorie_permis_id',
        'categorie_permis_extensible_id',
    ];


    // Relation Many-to-One avec la table "categorie_permis" pour la catégorie de permis principale
    public function categoriePrincipale()
    {
        return $this->belongsTo(CategoriePermis::class, 'categorie_permis_id', 'id');
    }

    // Relation Many-to-One avec la table "categorie_permis" pour la catégorie de permis extensible
    public function categorieExtensible()
    {
        return $this->belongsTo(CategoriePermis::class, 'categorie_permis_extensible_id', 'id');
    }
}
