<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echange extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "echanges";

    protected $guarded = [];
}
