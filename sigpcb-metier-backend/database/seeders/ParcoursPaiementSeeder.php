<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ParcoursPaiementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        DB::table('parcours_suivis')->insert([

            ['service'=>'Paiement','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '-1']),'candidat_id'=>'1','categorie_permis_id'=>'5','npi'=>'1234567890','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'1'],
            ['service'=>'Paiement','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '-1']),'candidat_id'=>'2','categorie_permis_id'=>'5','npi'=>'9876543211','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'2'],
            ['service'=>'Paiement','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '-1']),'candidat_id'=>'3','categorie_permis_id'=>'5','npi'=>'1111111111','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'3'],
            ['service'=>'Paiement','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '-1']),'candidat_id'=>'4','categorie_permis_id'=>'5','npi'=>'1934567890','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'4'],
            ['service'=>'Paiement','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '-1']),'candidat_id'=>'5','categorie_permis_id'=>'5','npi'=>'1123456789','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'5'],
            ['service'=>'Paiement','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '-1']),'candidat_id'=>'6','categorie_permis_id'=>'5','npi'=>'1123456089','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'6'],


            // ['service'=>'Validation ANaTT','candidat_id'=>'1','categorie_permis_id'=>'1','npi'=>'1234567890','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'1'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'2','categorie_permis_id'=>'1','npi'=>'9876543210','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'2'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'3','categorie_permis_id'=>'1','npi'=>'1111111111','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'3'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'4','categorie_permis_id'=>'1','npi'=>'1934567890','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'4'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'5','categorie_permis_id'=>'1','npi'=>'1123456789','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'5'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'6','categorie_permis_id'=>'1','npi'=>'1123456089','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'6'],
        ]); */
    }
}