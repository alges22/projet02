<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ParcoursSuiviSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /*  DB::table('parcours_suivis')->insert([
            ['service'=>'Préinscription','candidat_id'=>'1','categorie_permis_id'=>'5','npi'=>'1234567890','slug'=>'preinscription','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '0']),'message'=>"Votre demande de préinscription à l'examen du Permis de conduire catégorie B a été effectuée avec succès",'date_action'=>'2023-07-20','dossier_candidat_id'=>'1'],
            ['service'=>'Préinscription','candidat_id'=>'2','categorie_permis_id'=>'5','npi'=>'9876543211','slug'=>'preinscription','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '0']),'message'=>"Votre demande de préinscription à l'examen du Permis de conduire catégorie B a été effectuée avec succès",'date_action'=>'2023-07-20','dossier_candidat_id'=>'2'],
            ['service'=>'Préinscription','candidat_id'=>'3','categorie_permis_id'=>'5','npi'=>'1111111111','slug'=>'preinscription','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '0']),'message'=>"Votre demande de préinscription à l'examen du Permis de conduire catégorie B a été effectuée avec succès",'date_action'=>'2023-07-20','dossier_candidat_id'=>'3'],
            ['service'=>'Préinscription','candidat_id'=>'4','categorie_permis_id'=>'5','npi'=>'1934567890','slug'=>'preinscription','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '0']),'message'=>"Votre demande de préinscription à l'examen du Permis de conduire catégorie B a été effectuée avec succès",'date_action'=>'2023-07-20','dossier_candidat_id'=>'4'],
            ['service'=>'Préinscription','candidat_id'=>'5','categorie_permis_id'=>'5','npi'=>'1123456789','slug'=>'preinscription','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '0']),'message'=>"Votre demande de préinscription à l'examen du Permis de conduire catégorie B a été effectuée avec succès",'date_action'=>'2023-07-20','dossier_candidat_id'=>'5'],
            ['service'=>'Préinscription','candidat_id'=>'6','categorie_permis_id'=>'5','npi'=>'1123456089','slug'=>'preinscription','bouton' => json_encode(['bouton' => 'Paiement', 'status' => '0']),'message'=>"Votre demande de préinscription à l'examen du Permis de conduire catégorie B a été effectuée avec succès",'date_action'=>'2023-07-20','dossier_candidat_id'=>'6'],

            // ['service'=>'Monitoring','candidat_id'=>'1','categorie_permis_id'=>'1','npi'=>'1234567890','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L\'auto école ABC vient d\'enrégistrer votre formation, vous pouvez a procéder au paiement de vos frais d\'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'1'],
            // ['service'=>'Monitoring','candidat_id'=>'2','categorie_permis_id'=>'1','npi'=>'9876543210','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L\'auto école ABC vient d\'enrégistrer votre formation, vous pouvez a procéder au paiement de vos frais d\'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'2'],
            // ['service'=>'Monitoring','candidat_id'=>'3','categorie_permis_id'=>'1','npi'=>'1111111111','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L\'auto école ABC vient d\'enrégistrer votre formation, vous pouvez a procéder au paiement de vos frais d\'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'3'],
            // ['service'=>'Monitoring','candidat_id'=>'4','categorie_permis_id'=>'1','npi'=>'1934567890','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L\'auto école ABC vient d\'enrégistrer votre formation, vous pouvez a procéder au paiement de vos frais d\'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'4'],
            // ['service'=>'Monitoring','candidat_id'=>'5','categorie_permis_id'=>'1','npi'=>'1123456789','slug'=>'monitoring','auto_ecole_id'=>'6','message'=>"L\'auto école ABC vient d\'enrégistrer votre formation, vous pouvez a procéder au paiement de vos frais d\'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'5'],
            // ['service'=>'Monitoring','candidat_id'=>'6','categorie_permis_id'=>'1','npi'=>'1123456089','slug'=>'monitoring','auto_ecole_id'=>'1','message'=>"L\'auto école ABC vient d\'enrégistrer votre formation, vous pouvez a procéder au paiement de vos frais d\'inscription",'date_action'=>'2023-07-21','dossier_candidat_id'=>'6'],


            // ['service'=>'Paiement','candidat_id'=>'1','categorie_permis_id'=>'1','npi'=>'1234567890','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d\'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'1'],
            // ['service'=>'Paiement','candidat_id'=>'2','categorie_permis_id'=>'1','npi'=>'9876543210','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d\'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'2'],
            // ['service'=>'Paiement','candidat_id'=>'3','categorie_permis_id'=>'1','npi'=>'1111111111','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d\'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'3'],
            // ['service'=>'Paiement','candidat_id'=>'4','categorie_permis_id'=>'1','npi'=>'1934567890','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d\'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'4'],
            // ['service'=>'Paiement','candidat_id'=>'5','categorie_permis_id'=>'1','npi'=>'1123456789','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d\'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'5'],
            // ['service'=>'Paiement','candidat_id'=>'6','categorie_permis_id'=>'1','npi'=>'1123456089','slug'=>'paiement-inscription','message'=>"Paiement de vos frais d\'inscription éffectué avec succès",'date_action'=>'2023-07-22','dossier_candidat_id'=>'6'],


            // ['service'=>'Validation ANaTT','candidat_id'=>'1','categorie_permis_id'=>'1','npi'=>'1234567890','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'1'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'2','categorie_permis_id'=>'1','npi'=>'9876543210','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'2'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'3','categorie_permis_id'=>'1','npi'=>'1111111111','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'3'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'4','categorie_permis_id'=>'1','npi'=>'1934567890','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'4'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'5','categorie_permis_id'=>'1','npi'=>'1123456789','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'5'],
            // ['service'=>'Validation ANaTT','candidat_id'=>'6','categorie_permis_id'=>'1','npi'=>'1123456089','slug'=>'validation-anatt','message'=>"L\'ANaTT vient de valider votre dossier pour la catégorie de permis de conduire B ",'date_action'=>'2023-07-23','dossier_candidat_id'=>'6'],
        ]); */
    }
}