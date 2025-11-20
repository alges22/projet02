<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int|null $ua_parent_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UniteAdmin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ua_parent_id',
        'sigle',
        'status',
    ];


    protected $casts = [
        'id' => 'integer',
        'ua_parent_id' => 'integer',
    ];

    public function tutelle()
    {
        return $this->belongsTo(UniteAdmin::class, 'ua_parent_id');
    }

    public function children()
    {
        return $this->hasMany(UniteAdmin::class, 'ua_parent_id');
    }
}
