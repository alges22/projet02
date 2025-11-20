<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoniteurOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'moniteur_id',
        'code',
        'expire',
        'action',
        'nombre_de_fois',
        'retry_times',
    ];

    protected $casts = [
        'expire' => 'datetime',
        'retry_times' => 'datetime',
    ];

    protected $table = "moniteur_otps";
}