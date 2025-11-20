<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Api;
use App\Models\SuiviCandidat;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MonitoringSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $dossiers = Api::data(Api::base("GET", "dossier-sessions"))['paginate_data']['data'];
        $langues = Api::data(Api::base("GET", "langues"));

        $autoEcoleId = 6;

        foreach ($dossiers as $key => $candidat) {
            // Random index for $chapitres array
            $randomLangueIndex = array_rand($langues);
            $langueId = $langues[$randomLangueIndex]['id'];

            // Random index for $annexIds array

            // Random index for $states array
            $state = $candidat['state'];

            $d = SuiviCandidat::create([
                'auto_ecole_id' => $autoEcoleId,
                "categorie_permis_id" => $candidat['categorie_permis_id'],
                "langue_id" => $langueId,
                "examen_id" => $candidat['examen_id'] ?? null,
                "annexe_id" => $candidat['annexe_id'],
                "dossier_candidat_id" => $candidat['dossier_candidat_id'],
                "dossier_session_id" => $candidat['id'],
                "chapitres_id" => '1,2,3',
                "npi" => $candidat['npi'],
                "certification" => true,
                "state" => $state == 'payment' ? "pending" : ($state == 'pending' ? 'init' : $state),
            ]);
        }
    }
}
