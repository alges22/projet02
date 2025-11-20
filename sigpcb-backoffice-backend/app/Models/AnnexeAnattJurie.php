<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnexeAnattJurie extends Model
{
    use HasFactory;
    protected $fillable = [
        'annexe_anatt_id',
        'name',
    ];
    
    public function annexe()
    {
        return $this->belongsTo(AnnexeAnatt::class, 'annexe_anatt_id');
    }
}
