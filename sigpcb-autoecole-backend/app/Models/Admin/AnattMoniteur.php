<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnattMoniteur extends Model
{
    use HasFactory;
    protected $table = "moniteurs";
    protected $connection = "admin";
}