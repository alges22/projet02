<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Api;
use App\Models\ConvocationCode;
use App\Models\DossierCandidat;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CompoDbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->call(UsersSeeder::class);
        $aLaisser = ['1234567890', '9876543210'];
        $permis = Api::data(Api::base("GET", "chapitres"));
        $langues = Api::data(Api::base("GET", "langues"));


        $users = User::all();
        foreach ($users as $key => $candidat) {
            $permisId = 4;
            $autoEcoleId = rand(1, 6);

            // Random index for $langues array
            //$randomLangueIndex = array_rand($langues);
            $langueId = 1; //Fançais

            // Random index for $states array
            $states = ['validate'];
            $randomStateIndex = array_rand($states);
            $state = $states[$randomStateIndex];

            $exemenId = 2;
            $annexeId = 1;
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
            $resultat_code = null;

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
                "state" => 'validate',
                'presence' => $presence,
            ]);
            // "examen_id" => rand(7, 12),


        }
    }
}
