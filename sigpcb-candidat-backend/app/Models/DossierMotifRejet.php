<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DossierMotifRejet extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $table = 'dossier_motif_rejets';

    protected $fillable = [
        'motif',
        'dossier_candidat_id',
        'date_rejet',
        'date_soumission',
        'date_decision'
    ];
}
