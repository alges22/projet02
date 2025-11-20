<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DossierSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Nombre de dossiers à générer
        $dossierCount = 10;

        for ($i = 1; $i <= $dossierCount; $i++) {
            DB::table('dossier_sessions')->insert([
                'npi' => str_pad(mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
                'montant_paiement' => null,
                'is_militaire' => 'bye',
                'restriction_medical' => null,
                'date_payment' => null,
                'date_validation' => null,
                'resultat_conduite' => null,
                'resultat_code' => 'success',
                'bouton_paiement' => '-1',
                'state' => 'validate',
                'closed' => false,
                'is_paid' => false,
                'fiche_medical' => null,
                'type_examen' => 'code-conduite',
                'presence' => 'present',
                'presence_conduite' => null,
                'langue_id' => rand(1, 3),
                'auto_ecole_id' => rand(1, 5),
                'annexe_id' => 7,
                'examen_id' => 44,
                'categorie_permis_id' => rand(1, 11),
                'permis_prealable_dure' => null,
                'abandoned' => false,
                'permis_prealable_id' => null,
                'permis_extension_id' => null,
                'dossier_candidat_id' => $i,
                'old_ds_rejet_id' => null,
                'old_ds_justif_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'date_inscription' => now(),
            ]);
        }
    }
}
