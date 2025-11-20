<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ParcoursAutoecoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /*  DB::table('parcours_suivis')->insert([

            ['service'=>'Monitoring','candidat_id'=>'1','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '1']),'categorie_permis_id'=>'5','npi'=>'1234567890','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L'auto école ABC vient d'enrégistrer votre formation vous pouvez a procéder au paiement de vos frais d'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'1'],
            ['service'=>'Monitoring','candidat_id'=>'2','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '1']),'categorie_permis_id'=>'5','npi'=>'9876543211','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L'auto école ABC vient d'enrégistrer votre formation vous pouvez a procéder au paiement de vos frais d'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'2'],
            ['service'=>'Monitoring','candidat_id'=>'3','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '1']),'categorie_permis_id'=>'5','npi'=>'1111111111','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L'auto école ABC vient d'enrégistrer votre formation vous pouvez a procéder au paiement de vos frais d'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'3'],
            ['service'=>'Monitoring','candidat_id'=>'4','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '1']),'categorie_permis_id'=>'5','npi'=>'1934567890','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L'auto école ABC vient d'enrégistrer votre formation vous pouvez a procéder au paiement de vos frais d'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'4'],
            ['service'=>'Monitoring','candidat_id'=>'5','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '1']),'categorie_permis_id'=>'5','npi'=>'1123456789','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L'auto école ABC vient d'enrégistrer votre formation vous pouvez a procéder au paiement de vos frais d'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'5'],
            ['service'=>'Monitoring','candidat_id'=>'6','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '1']),'categorie_permis_id'=>'5','npi'=>'1123456089','slug'=>'monitoring','auto_ecole_id'=>'1','message'=>"L'auto école ABC vient d'enrégistrer votre formation vous pouvez a procéder au paiement de vos frais d'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'6'],

        ]); */
    }
}