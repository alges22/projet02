<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultatCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidat_salle_id',
        'note',
        'passed',
    ];
    
    public function candidatSalle()
    {
        return $this->belongsTo(CandidatExamenSalle::class, 'candidat_salle_id');
    }
}
