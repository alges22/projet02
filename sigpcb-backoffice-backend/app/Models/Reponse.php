<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    protected $connection = "base";
    protected $fillable = [
        'name', 'couleur'
    ];

    protected $table = 'reponses';

    public $timestamps = true;

    protected $guarded = ['id'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function getNameAttribute($value)
    {
        return strtoupper($value);
    }

    public function scopeName($query, $name)
    {
        return $query->where('name', strtoupper($name));
    }
}


