<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Impersonation extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "impersonations";
    protected $guarded = [];
}
