<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'name' => 'role',
                'guard_name' => 'web',
            ],
            [
                'name' => 'role-add',
                'guard_name' => 'web',
            ],
            [
                'name' => 'role-list',
                'guard_name' => 'web',
            ],
            [
                'name' => 'permission',
                'guard_name' => 'web',
            ],
            [
                'name' => 'permission-add',
                'guard_name' => 'web',
            ],
            [
                'name' => 'permission-list',
                'guard_name' => 'web',
            ]
        ];

        foreach ($permissions as $value) {
            Permission::create($value);
        }
    }
}
