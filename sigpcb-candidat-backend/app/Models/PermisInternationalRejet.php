<?php

namespace App\Models;

use App\Models\PermisInternational;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PermisInternationalRejet extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $fillable = [
        'permis_international_id',
        'motif',
        'date_validation',
        'date_correction',
        'state',
    ];
    public function demandePemrisInternational()
    {
        return $this->belongsTo(PermisInternational::class);
    }
}
