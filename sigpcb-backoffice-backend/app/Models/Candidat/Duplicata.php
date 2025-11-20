<?php

namespace App\Models\Candidat;

use App\Models\AnnexeAnatt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Duplicata extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "duplicatas";

    protected $guarded = [];

    public function annexeAnatt()
    {
        return $this->belongsTo(AnnexeAnatt::class, 'annexe_id');
    }
}
