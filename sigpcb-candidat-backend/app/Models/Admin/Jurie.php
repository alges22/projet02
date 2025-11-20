<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurie extends Model
{
    use HasFactory;
    protected $connection  = "admin";
    protected $table = "juries";

    protected $guarded = [];

    public function withExaminateur()
    {
        $examinateur = Examinateur::find($this->examinateur_id);
        $this->setAttribute('examinateur', $examinateur);
        return $this;
    }

    /**
     * Ajoute l'annexe
     *
     * @param array $attrs
     * @return $this
     */
    public function withAnnexe($attrs = ['*'])
    {
        $annexe = AnnexeAnatt::find($this->annexe_anatt_id, $attrs);

        $this->setAttribute("annexe", $annexe);

        return $this;
    }
}
