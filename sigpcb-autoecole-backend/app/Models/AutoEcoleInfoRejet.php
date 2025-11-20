<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoEcoleInfoRejet extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'date_correction' => 'datetime'
    ];

    public function autoEcoleInfo()
    {
        return $this->belongsTo(AutoEcoleInfo::class);
    }
}
