<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrancheAge extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "tranche_ages";
    protected $guarded = [];
}
