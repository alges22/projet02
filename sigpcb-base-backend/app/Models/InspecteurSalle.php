<?php

namespace App\Models;

use App\Models\Admin\Examen;
use App\Models\Admin\Inspecteur;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspecteurSalle extends Model
{
    use HasFactory;
    protected   $connection = "admin";
    protected $guarded = [];

    public function withInspecteur()
    {
        $this->setAttribute('inspecteur', Inspecteur::find($this->inspecteur_id));

        return $this;
    }

    public function withExamen()
    {
        $this->setAttribute('examen', Examen::find($this->examen_id));

        return $this;
    }
}
