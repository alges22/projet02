<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Api;
use App\Models\DossierSession;
use App\Models\ConvocationCode;
use App\Models\DossierCandidat;
use Illuminate\Database\Seeder;

class DossierCandidatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

        $aLaisser = ['1234567890', '9876543210'];
        $permis = Api::data(Api::base("GET", "chapitres"));
        $langues = Api::data(Api::base("GET", "langues"));


        $users = User::all();
        foreach ($users as $key => $candidat) {
            $permisId = rand(1, 8);
            $autoEcoleId = rand(1, 6);

            // Random index for $langues array
            //$randomLangueIndex = array_rand($langues);
            $langueId = rand(1, 3);

            // Random index for $states array
            $states = ['validate', "payment", 'init', 'rejet'];
            $randomStateIndex = array_rand($states);
            $state = $states[$randomStateIndex];

            $exemenId = rand(1, 4);
            $annexeId = rand(1, 3);
            $d = DossierCandidat::create([
                'categorie_permis_id' =>
                $permisId,
                "candidat_id" => $candidat->id,
                "npi" => $candidat->npi,
                "groupage_test" => "/path",
                "group_sanguin" => "O+",
                "is_militaire" => "civil",
            ]);
            $presence = null;
            $resultat_codes = ['success', 'failed'];

            if ($state == 'validate') {
                $i = rand(0, 1);
                $resultat_code =  $resultat_codes[$i];
            } else {
                $resultat_code =  null;
            }
            $ds = $d->dossierSessions()->create([
                "npi" => $candidat->npi,
                "restriction_medical" => 1,
                "fiche_medical" => "/public/fiche",
                "langue_id" => $langueId,
                "auto_ecole_id" => $autoEcoleId,
                "is_militaire" => "civil",
                "annexe_id" => $annexeId,
                "resultat_code" => $resultat_code,
                "examen_id" => $exemenId,
                "type_examen" => "code-conduite",
                'categorie_permis_id' =>
                $permisId,
                "state" => $state,
                'presence' => $presence,
            ]);
            // "examen_id" => rand(7, 12),

            # Convocation
            ConvocationCode::create([
                "code" => "000000",
                "dossier_session_id" => $ds->id
            ]);
        }
    }
}