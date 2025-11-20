<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurie extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'annexe_anatt_id',
        'examinateur_id',
        'examen_id',
        'annexe_jury_id',
        'closed'
    ];
    public function examinateur()
    {
        return $this->belongsTo(Examinateur::class);
    }
    public function annexe()
    {
        return $this->belongsTo(AnnexeAnatt::class, 'annexe_anatt_id');
    }
}