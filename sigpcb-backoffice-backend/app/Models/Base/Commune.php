<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Commune extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "communes";
    protected $guarded = [];
    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }
}
