<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Models\User $user
 */
class Inspecteur extends Model
{
    use HasFactory;

    protected $fillable= ['user_id','agent_id','annexe_anatt_id'] ;
    /**
     * Get the user that belongs to the inspecteur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function annexe()
    {
        return $this->belongsTo(AnnexeAnatt::class, 'annexe_anatt_id');
    }
    
}
