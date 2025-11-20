<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recrutement extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "recrutements";

    protected $guarded = [];

}
