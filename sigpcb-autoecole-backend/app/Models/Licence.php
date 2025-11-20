<?php

namespace App\Models;

use App\Models\AutoEcole;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property Carbon $date_fin
 */
class Licence extends Model
{
    use HasFactory;

    protected $fillable = [
        'auto_ecole_id',
        'status',
        'date_debut',
        'date_fin',
        'code'
    ];

    protected $casts = [
        'date_debut' => "datetime",
        'date_fin' => "datetime",
    ];

    protected $table = "auto_ecole_licences";
    public function autoEcole()
    {
        return $this->belongsTo(AutoEcole::class);
    }

    protected function status(): Attribute
    {
        return  new Attribute(get: fn ($status) => $this->date_fin->isFuture());
    }
}