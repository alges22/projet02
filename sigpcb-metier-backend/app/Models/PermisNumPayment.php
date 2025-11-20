<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisNumPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidat_id',
        'npi',
        'auto_ecole_id',
        'agregateur',
        'description',
        'transaction_id',
        'reference',
        'mode',
        'operation',
        'transaction_key',
        'montant',
        'phone_payment',
        'ref_operateur',
        'numero_recu',
        'moyen_payment',
        'status',
        'num_transaction',
        'date_payment',
        'dossier_candidat_id',
        'dossier_session_id',
        'categorie_permis_id',
    ];

    protected $dates = ['date_payment'];

    public function parcourssuivi()
    {
        return $this->hasMany(ParcoursSuivi::class);
    }
}
