<?php

namespace App\Models\Base;

use App\Models\Candidat\DossierSession;
use App\Models\Examen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispensePaiement extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "dispense_paiements";
    protected $guarded = [];

        // Définir la relation avec Examen
        public function examen()
        {
            return $this->belongsTo(Examen::class); // Une dispense appartient à un examen
        }

        // Définir la relation avec DossierSession
        public function dossierSession()
        {
            return $this->belongsTo(DossierSession::class); // Une dispense appartient à une session de dossier
        }

        public function validator()
        {
            return $this->belongsTo(User::class,'validator_id');
        }
}
