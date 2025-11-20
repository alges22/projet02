<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatPayment extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $fillable = [
        'candidat_id',
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
        'examen_id'
    ];

    protected $dates = ['date_payment'];

    public function candidat()
    {
        return $this->belongsTo(User::class, 'candidat_id');
    }

    public function dossierCandidat()
    {
        return $this->belongsTo(DossierCandidat::class, 'dossier_candidat_id');
    }
}
