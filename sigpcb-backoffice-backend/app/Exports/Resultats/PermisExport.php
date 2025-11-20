<?php

namespace App\Exports\Resultats;

use App\Models\Permis;
use Illuminate\Support\Arr;
use App\Services\GetCandidat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;

class PermisExport implements FromCollection
{
    public function __construct(protected array $data) {}
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $headers = [
            ['numero_matricule_permis', 'NOM ET PRENOMS', 'NPI', 'GROUPE SANGUIN', 'TELEPHONE', 'CATEGORIE', 'DATE DELIVRANCE', 'DATE EXPIRATION', 'DATE NAISSANCE', 'numero_matricule', 'NUMERO DU PERMIS'],
        ];

        $filters = Arr::only($this->data, ['examen_id', 'auto_ecole_id', 'categorie_permis_id', 'annexe_id']);

        # Récupération des dossiers valides
        /**
         * @var Collection<int, Permis>
         */
        $admis = Permis::with(['categoriePermis', 'dossierSession.dossier'])->filter($filters)->orderBy("delivered_at")->get();

        $npis = $admis->pluck("npi")->unique()->all();
        $infos = GetCandidat::get($npis);

        $collection = collect()->push($headers);

        foreach ($admis as $key => $p) {

            $item = [];
            $p->withCandidat($infos);
            $candidat = $p->candidat;

            # numero_matricule_permis
            $item[0] = null;

            # NOM ET PRENOMS
            $item[1] = data_get($candidat, "nom") . " " . data_get($candidat, "prenoms");

            # NPI
            $item[2] = data_get($candidat, "npi");

            # GROUPE SANGUIN
            $item[3] = $p->dossierSession->dossier?->group_sanguin;

            # TELEPHONE
            $item[4] = data_get($candidat, "telephone");

            # CATEGORIE
            $item[5] = $p->categoriePermis->name;

            # DATE DELIVRANCE
            $item[6] = Carbon::parse($p->delivered_at)->format("d/m/Y");

            # DATE EXPIRATION
            $item[7] = Carbon::parse($p->expired_at)->format("d/m/Y");
            if ($p->categoriePermis->name == 'B') {
                $item[7] = "permanent";
            }

            # DATE NAISSANCE
            $item[8] = null;
            $date_de_naissance = data_get($candidat, "date_de_naissance");
            if ($date_de_naissance) {
                $item[8] = Carbon::parse($date_de_naissance)->format("d/m/Y");
            }

            # numero_matricule
            $item[9] = null;

            # NUMERO DU PERMIS
            $item[10] = $p->code_permis;

            $collection->push($item);
        }

        return $collection;
    }

    public function generate()
    {
        $filename = Storage::path('liste-resultat-permis.xlsx');
        Excel::store(new static($this->data), $filename);
        return route('download', ['token' => encrypt($filename)]);
    }
}
