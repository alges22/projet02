<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatPayment extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected  $table = "candidat_payments";

    protected $guarded = [];

    public function withCandidat(array $candidats)
    {
        $this->withNpi();
        $candidat = collect($candidats)->where('npi', $this->npi)->first();
        $this->setAttribute('candidat', $candidat);
        return $this;
    }

    public function withNpi()
    {
        $ds = DossierSession::find($this->dossier_session_id, ['npi']);
        $this->setAttribute('npi', $ds->npi);
        return $this;
    }
}
