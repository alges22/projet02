<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parametre extends Model
{
    protected $table = 'parametres';

    protected $fillable = [
        'delai_conduite',
        'delai_ajounment_desertion',
        'delai_correction_situation_ae',
    ];


}
