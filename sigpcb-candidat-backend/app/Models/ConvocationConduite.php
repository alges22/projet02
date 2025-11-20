<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvocationConduite extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $connection = "base";
}
