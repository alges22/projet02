<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispensePaiement extends Model
{
    use HasFactory;

    // Nom de la table associée au modèle
    protected $table = 'dispense_paiements';

    // Définir les attributs qui peuvent être assignés massivement
    protected $fillable = [
        'validated_at',
        'rejeted_at',
        'validator_npi',
        'created_by',
        'validator_id',
        'status',
        'used_at',
        'examen_id',
        'candidat_npi',
        'dossier_session_id',
        'note',
    ];

    // Définir la façon dont les dates sont traitées
    protected $dates = [
        'validated_at',
        'rejeted_at',
        'used_at',
        'created_at',
        'updated_at',
    ];

    // Optionnel: définir des conversions de types si nécessaire
    protected $casts = [
        'examen_id' => 'integer',
        'candidat_npi' => 'integer',
        'dossier_session_id' => 'integer',
        'validator_id' => 'integer',
        'status' => 'string',
    ];
}
