<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ResultatCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*  DB::table('parcours_suivis')->insert([

            ['service'=>'Résultat Code','bouton' => json_encode(['bouton' => 'convocation-conduite', 'status' => '1']),'candidat_id'=>'1','categorie_permis_id'=>'5','npi'=>'1234567890','slug'=>'resultat-code','message'=>"Félicitation vous avez réussi au code pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-24','dossier_candidat_id'=>'1'],
            ['service'=>'Résultat Code','bouton' => json_encode(['bouton' => 'convocation-conduite', 'status' => '1']),'candidat_id'=>'2','categorie_permis_id'=>'5','npi'=>'9876543211','slug'=>'resultat-code','message'=>"Félicitation vous avez réussi au code pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-24','dossier_candidat_id'=>'2'],
            ['service'=>'Résultat Code','bouton' => json_encode(['bouton' => 'convocation-conduite', 'status' => '1']),'candidat_id'=>'3','categorie_permis_id'=>'5','npi'=>'1111111111','slug'=>'resultat-code','message'=>"Félicitation vous avez réussi au code pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-24','dossier_candidat_id'=>'3'],
            ['service'=>'Résultat Code','bouton' => json_encode(['bouton' => 'convocation-conduite', 'status' => '1']),'candidat_id'=>'4','categorie_permis_id'=>'5','npi'=>'1934567890','slug'=>'resultat-code','message'=>"Félicitation vous avez réussi au code pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-24','dossier_candidat_id'=>'4'],
            ['service'=>'Résultat Code','bouton' => json_encode(['bouton' => 'convocation-conduite', 'status' => '1']),'candidat_id'=>'5','categorie_permis_id'=>'5','npi'=>'1123456789','slug'=>'resultat-code','message'=>"Félicitation vous avez réussi au code pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-24','dossier_candidat_id'=>'5'],
            ['service'=>'Résultat Code','bouton' => json_encode(['bouton' => 'convocation-conduite', 'status' => '1']),'candidat_id'=>'6','categorie_permis_id'=>'5','npi'=>'1123456089','slug'=>'resultat-code','message'=>"Félicitation vous avez réussi au code pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-24','dossier_candidat_id'=>'6'],
        ]); */
    }
}