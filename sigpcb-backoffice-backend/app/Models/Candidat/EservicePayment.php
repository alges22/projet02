<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EservicePayment extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "eservice_payments";

    protected $guarded = [];
}
