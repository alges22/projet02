<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapQuestCount extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "chap_question_counts";
    protected $guarded = [];
}
