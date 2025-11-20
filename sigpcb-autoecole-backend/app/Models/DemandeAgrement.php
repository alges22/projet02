<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DemandeAgrement
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $state
 * @property string $auto_ecole
 * @property string $ifu
 * @property int $departement_id
 * @property int $commune_id
 * @property string|null $quartier
 * @property string|null $ilot
 * @property string|null $parcelle
 * @property string $moniteurs
 * @property array $vehicules
 * @property string $telephone_pro
 * @property string $email_pro
 * @property string $email_pro
 * @property string $email_promoteur
 * @property int $promoteur_npi
 * @property  DemandeAgrementFile $fiche
 * @property  User $promoteur
 */
class DemandeAgrement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state',
        'auto_ecole',
        'ifu',
        'departement_id',
        'commune_id',
        'quartier',
        'ilot',
        'parcelle',
        'moniteurs',
        'telephone_pro',
        'email_pro',
        'email_promoteur',
        'promoteur_npi',
        'promoteur_id',
        "vehicules"

    ];

    public function fiche()
    {
        return $this->hasOne(DemandeAgrementFile::class);
    }

    public function  promoteur()
    {
        return $this->belongsTo(User::class);
    }

    public function agrement()
    {
        return $this->hasOne(Agrement::class);
    }
}
