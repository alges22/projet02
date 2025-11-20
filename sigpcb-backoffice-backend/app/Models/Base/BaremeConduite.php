<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class BaremeConduite extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "bareme_conduites";
    protected $guarded = [];

    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }
}
