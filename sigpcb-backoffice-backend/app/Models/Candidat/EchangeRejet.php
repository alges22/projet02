<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EchangeRejet extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "echange_rejets";

    protected $guarded = [];
}
