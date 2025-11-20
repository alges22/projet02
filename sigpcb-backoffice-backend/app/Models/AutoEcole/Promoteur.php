<?php

namespace App\Models\AutoEcole;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promoteur extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "promoteurs";
    protected $guarded = [];
}
