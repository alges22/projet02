<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'type_action',
        'user_id',
        'description',
        'ip',
        'candidat_id'
    ];
}
