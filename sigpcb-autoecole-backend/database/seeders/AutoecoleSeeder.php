<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AutoecoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('auto_ecoles')->insert([
            ['name' => 'Experencia', 'adresse' => 'Cotonou', 'email' => 'Experencia@gmail.com', 'num_ifu' => '123456789', 'promoteur_name' => 'Aziz Loko', 'phone' => '62700000', 'password' => bcrypt('123456'), 'code' => '123456', 'commune_id' => '1', 'departement_id' => '1', 'departement_name' => 'Atlantique', 'annee_creation' => "2019"],
            ['name' => 'La vie', 'adresse' => 'Cotonou', 'email' => 'Lavie@gmail.com', 'num_ifu' => '123456788', 'promoteur_name' => 'Aziz Loko', 'phone' => '62700001', 'password' => bcrypt('123456'), 'code' => '123457', 'commune_id' => '2', 'departement_id' => '2', 'departement_name' => 'Atlantique', 'annee_creation' => "2018"],
            ['name' => 'Le code', 'adresse' => 'cocotomey', 'email' => 'Lecode@gmail.com', 'num_ifu' => '123456787', 'promoteur_name' => 'Aziz Loko', 'phone' => '62700002', 'password' => bcrypt('123456'), 'code' => '123458', 'commune_id' => '3', 'departement_id' => '3', 'departement_name' => 'Atlantique', 'annee_creation' => "2020"],
            ['name' => 'Experentia', 'adresse' => 'Cotonou', 'email' => 'Experentia@gmail.com', 'num_ifu' => '123456786', 'promoteur_name' => 'Aziz Loko', 'phone' => '62700003', 'password' => bcrypt('123456'), 'code' => '123459', 'commune_id' => '1', 'departement_id' => '1', 'departement_name' => 'Atlantique', 'annee_creation' => "2019"],
            ['name' => 'Liberty', 'adresse' => 'Cotonou', 'email' => 'Liberty@gmail.com', 'num_ifu' => '123456785', 'promoteur_name' => 'Aziz Loko', 'phone' => '62700004', 'password' => bcrypt('123456'), 'code' => '123450', 'commune_id' => '48', 'departement_id' => '8', 'departement_name' => 'Atlantique', 'annee_creation' => "2012"],
            ['name' => 'ABC', 'adresse' => 'Cotonou', 'email' => 'ABC@gmail.com', 'num_ifu' => '123456784', 'promoteur_name' => 'Aziz Loko', 'phone' => '62700005', 'password' => bcrypt('123456'), 'code' => '123406', 'commune_id' => '48', 'departement_id' => '8', 'departement_name' => 'Atlantique', 'annee_creation' => "2021"],
        ]);
    }
}
