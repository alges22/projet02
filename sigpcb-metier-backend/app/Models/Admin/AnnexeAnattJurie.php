<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnexeAnattJurie extends Model
{
    use HasFactory;
    protected $connection  = "admin";
    protected $table = "annexe_anatt_juries";
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
