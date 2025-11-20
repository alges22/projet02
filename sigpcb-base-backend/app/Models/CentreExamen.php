<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentreExamen extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'commune_id', 'status'];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

}
