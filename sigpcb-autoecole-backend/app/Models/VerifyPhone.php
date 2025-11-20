<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyPhone extends Model
{
    use HasFactory;

    protected $fillable = [
        'ifu',
        'npi',
        'code',
        'expired_at',
    ];

    protected $table = "verify_phones";
}