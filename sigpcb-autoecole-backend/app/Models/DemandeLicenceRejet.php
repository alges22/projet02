<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeLicenceRejet extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'date_correction' => 'datetime'
    ];
    public function demandeLicence()
    {
        return $this->belongsTo(DemandeLicence::class);
    }
}
