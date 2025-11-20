<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Langue extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "langues";
}
