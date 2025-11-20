<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(UsersSeeder::class);
        $this->call(DossierCandidatsSeeder::class);
        $this->call(ParcoursSuiviSeeder::class);
        $this->call(ParcoursAutoecoleSeeder::class);
        $this->call(ParcoursPaiementSeeder::class);
        $this->call(ParcoursValidatSeeder::class);
        $this->call(ResultatCodeSeeder::class);

    }
}
