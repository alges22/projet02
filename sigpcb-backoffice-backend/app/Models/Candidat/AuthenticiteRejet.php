<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticiteRejet extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "authenticite_rejets";

    protected $guarded = [];
}
