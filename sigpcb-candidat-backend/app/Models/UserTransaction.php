<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $connection = "base";
    protected $table = 'candidat_transactions';
}
