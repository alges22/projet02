<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create(['name' => 'super-admin','guard_name' => 'web','nom_complet' => 'Administrateur principal']);
        $role = Role::create(['name' => 'admin','guard_name' => 'web','nom_complet' => 'Administrateur']);

        $superAdminRole = Role::findByName('super-admin');
        $superAdminRole->givePermissionTo('all');
    }

}
