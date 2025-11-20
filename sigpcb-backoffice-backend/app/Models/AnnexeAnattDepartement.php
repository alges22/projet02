<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $annexe_anatt_id
 * @property string $departement_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AnnexeAnattDepartement extends Model
{
    use HasFactory;

    protected $fillable = [
        'annexe_anatt_id',
        'departement_id',
    ];



    public function annexeAnatt()
    {
        return $this->belongsTo(AnnexeAnatt::class);
    }
}