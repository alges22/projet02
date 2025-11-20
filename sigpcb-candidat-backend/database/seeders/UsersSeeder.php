<?php

namespace Database\Seeders;

use App\Services\Api;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $candidats = Api::data(Api::base("GET", 'candidats'));

        foreach ($candidats as $key => $candidat) {

            DB::table('users')->insert([
                ['npi' => $candidat['npi']],
            ]);
        }
    }
}
