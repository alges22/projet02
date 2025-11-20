<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examinateur extends Model
{
    use HasFactory;

    protected $fillable= ['user_id','agent_id','annexe_anatt_id'] ;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function annexe()
    {
        return $this->belongsTo(AnnexeAnatt::class, 'annexe_anatt_id');
    }
    
    public function examinateurCategoriePermis(){
        return $this->hasMany(ExaminateurCategoriePermis::class);
    }

}
