<?php

namespace App\Models;

use App\Models\Duplicata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DuplicataRejet extends Model
{
    use HasFactory;
    protected $fillable = [
        'duplicata_id',
        'motif',
        'date_validation',
        'date_correction',
        'state',
    ];
    public function demandeDuplicata()
    {
        return $this->belongsTo(Duplicata::class);
    }
}
