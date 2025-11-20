<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConduiteVague extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'id',
        'numero',
        'examen_id',
        'annexe_anatt_id',
        'date_compo',
        'closed'
    ];
    protected $casts = [
        "date_compo" => "date",
    ];

    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }

    public function langue()
    {
        return $this->belongsTo(Langue::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }
}