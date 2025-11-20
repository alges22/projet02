<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "entreprises";

    protected $guarded = [];

}
