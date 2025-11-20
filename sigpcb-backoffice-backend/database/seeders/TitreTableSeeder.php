<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use App\Models\Titre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TitreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('titres')->insert([
            ['name' => 'Monsieur'],
            ['name' => 'Madame'],
            ['name' => 'Directeur'],
            ['name' => 'Directrice'],
        ]);
    }
}
