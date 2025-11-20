<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ActeSignableSignataire
 * @package App\Models
 *
 * @property int $id
 * @property int $acte_signable_id
 * @property int $signataire_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ActeSignableSignataire extends Pivot
{
    use HasFactory;

    protected $table = "acte_signable_signataires";
    protected $fillable = [
        'acte_signable_id',
        'signataire_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'acte_signable_id' => 'integer',
        'signataire_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the acte signable associated with this signataire.
     */
    public function acteSignable()
    {
        return $this->belongsTo(ActeSignable::class);
    }

    /**
     * Get the signataire associated with this acte signable.
     */
    public function signataire()
    {
        return $this->belongsTo(Signataire::class);
    }
}
