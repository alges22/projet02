<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProrogationRejet extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "prorogation_rejets";

    protected $guarded = [];
}
