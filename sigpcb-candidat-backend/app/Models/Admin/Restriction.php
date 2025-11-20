<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    use HasFactory;
    protected $connection  = "admin";

    protected $table = "restrictions";
    protected $guarded = [];

}
