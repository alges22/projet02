<?php

namespace Database\Seeders;

use App\Models\UniteAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CompoDbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UniteAdmin::create(["name" => "Ministère de Tutelle"]);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(TitreTableSeeder::class);
        $this->call(AnnexeAnattsTableSeeder::class);
        $this->call(RestrictionSeeder::class);
        $this->call(UserTableSeeder::class);
    }
}
