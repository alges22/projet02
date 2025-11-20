<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcoursSuivi extends Model
{
    use HasFactory;

    protected $table = "parcours_suivis";

    protected $guarded = [];
}