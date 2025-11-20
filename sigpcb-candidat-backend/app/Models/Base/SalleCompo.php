<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SalleCompo extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "salle_compos";
}
