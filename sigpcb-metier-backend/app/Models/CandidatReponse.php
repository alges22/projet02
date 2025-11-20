<?php

namespace App\Models;

use App\Models\Candidat;
use Illuminate\Database\Eloquent\Model;

class CandidatReponse extends Model
{

    protected $guarded = [];

    public function candidat()
    {
        return $this->belongsTo(Candidat::class);
    }
}
