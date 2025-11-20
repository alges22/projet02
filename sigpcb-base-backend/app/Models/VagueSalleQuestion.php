<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VagueSalleQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['vague_id', 'salle_compo_id', 'questions', 'closed'];

    protected function questions(): Attribute
    {
        return new Attribute(
            get: fn ($questions) => array_map("intval", explode(",", $questions)),
        );
    }
}
