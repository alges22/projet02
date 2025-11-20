<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminateurNewI extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "users";

    protected $guarded = [];

}
