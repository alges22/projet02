<?php

namespace Database\Seeders;

use App\Services\Api;
use App\Models\Entreprise;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    // public function run()
    // {

    //     $candidats = Api::data(Api::anip("GET", 'candidats'));

    //     foreach ($candidats as $key => $candidat) {

    //         DB::table('users')->insert([
    //             ['npi' => $candidat['npi']],
    //         ]);
    //     }
    // }
    public function run()
    {

        $user1 = Entreprise::create([
            'name' => 'Jaurès',
            'phone' => '62700942',
            'email' => "ulrichjaures2@gmail.com",
            'password' => Hash::make('12345678'),
        ]);

        $user2 = Entreprise::create([
            'name' => 'Claude',
            'phone' => '61441378',
            'email' => "dev.claudy@gmail.com",
            'password' => Hash::make('12345678'),
        ]);

        $user3 = Entreprise::create([
            'name' => 'Franck',
            'phone' => '96261115',
            'email' => "franckhoundje@gmail.com",
            'password' => Hash::make('12345678'),
        ]);

        $user4 = Entreprise::create([
            'name' => 'Mayaa',
            'phone' => '61952803',
            'email' => "abonnementbj@gmail.com",
            'password' => Hash::make('12345678'),
        ]);

        $user5 = Entreprise::create([
            'name' => 'Gildas',
            'phone' => '66983958',
            'email' => "gildas.zinkpe@gmail.com",
            'password' => Hash::make('12345678'),
        ]);

        $user6 = Entreprise::create([
            'name' => 'Franck',
            'phone' => '41007400',
            'email' => "franckhouehou@gmail.com",
            'password' => Hash::make('12345678'),
        ]);

    }
}
