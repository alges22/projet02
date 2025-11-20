<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SyncRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::where('name', '!=', 'super-admin')->get();

        foreach ($roles as $key => $role) {
            $role->permissions()->detach();
            $role->permissions()->delete();
        }


        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();


        foreach (User::all() as $key => $user) {
            $user->permissions()->where('name', '!=', 'all')->detach();
            $user->permissions()->where('name', '!=', 'all')->delete();
        }

        Permission::where('name', '!=', 'all')->delete();
    }
}
