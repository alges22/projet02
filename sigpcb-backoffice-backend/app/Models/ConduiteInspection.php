<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ConduiteInspection
 * @package App\Models
 *
 * @property int $id
 * @property bool $status
 * @property string|null $observations
 * @property int $inspecteur_id
 * @property int $vague_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ConduiteInspection extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'observations', 'inspecteur_id', 'vague_id'];
    protected $casts = [
        'status' => 'boolean',
        'inspecteur_id' => 'integer',
        'vague_id' => 'integer',
    ];

    public function inspecteur()
    {
        return $this->belongsTo(Inspecteur::class);
    }
}
