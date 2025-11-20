<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Departement extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "departements";
}
