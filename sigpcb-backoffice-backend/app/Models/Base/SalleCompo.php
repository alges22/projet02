<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalleCompo extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $table = "salle_compos";
    protected $guarded = [];
}
