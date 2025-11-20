<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\UniteAdmin;
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

        UniteAdmin::create(["name" => "Ministère de Tutelle"]);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(TitreTableSeeder::class);
        $this->call(RestrictionSeeder::class);
        $this->call(UserTableSeeder::class);
    }
}
