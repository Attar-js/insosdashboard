<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'permissions' => ['role','role-add', 'role-list', 'permission', 'permission-add', 'permission-list']
            ],
            [
                'name' => 'demo_admin',
                'guard_name' => 'web',
                'permissions' => []
            ],
            [
                'name' => 'user',
                'guard_name' => 'web',
                'permissions' => []
            ]
        ];

        foreach ($roles as $key => $value) {
            $permission = $value['permissions'];
            unset($value['permissions']);
            $role = Role::create($value);
            $role->givePermissionTo($permission);
        }
    }
}
