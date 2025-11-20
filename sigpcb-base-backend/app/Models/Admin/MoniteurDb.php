<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoniteurDb extends Model
{
    use HasFactory;
    protected $connection  = "admin";
    protected $table = "moniteurs";

}