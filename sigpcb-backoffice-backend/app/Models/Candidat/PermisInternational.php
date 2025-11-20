<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisInternational extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "permis_internationals";

    protected $guarded = [];
}
