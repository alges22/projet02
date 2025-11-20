<?php

namespace App\Models;

use App\Models\Echange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EchangeRejet extends Model
{
    use HasFactory;
    protected $fillable = [
        'echange_id',
        'motif',
        'date_validation',
        'date_correction',
        'state',
    ];
    public function demandeEchange()
    {
        return $this->belongsTo(Echange::class);
    }
}
