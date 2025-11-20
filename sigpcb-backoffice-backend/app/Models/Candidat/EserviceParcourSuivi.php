<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EserviceParcourSuivi extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "eservice_parcour_suivis";

    protected $guarded = [];
}
