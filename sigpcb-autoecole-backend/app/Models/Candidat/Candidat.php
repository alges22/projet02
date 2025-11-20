<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Candidat extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "candidats";
    protected $guarded = [];

}
