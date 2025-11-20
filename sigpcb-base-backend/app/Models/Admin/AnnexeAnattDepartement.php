<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnexeAnattDepartement extends Admin
{
    use HasFactory;

    protected $table = "annexe_anatt_departements";
    protected $fillable = [
        'annexe_anatt_id',
        'departement_id',
    ];
}
