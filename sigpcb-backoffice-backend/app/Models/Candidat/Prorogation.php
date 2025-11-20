<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prorogation extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "prorogations";

    protected $guarded = [];
}
