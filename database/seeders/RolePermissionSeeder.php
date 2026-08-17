<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'students.view', 'students.create', 'students.edit', 'students.delete',
            'students.import', 'students.export',
            'results.view', 'results.enter', 'results.publish', 'results.export',
            'settings.manage', 'users.manage', 'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        $programmeOfficer = Role::firstOrCreate(['name' => 'programme_officer', 'guard_name' => 'web']);
        $programmeOfficer->syncPermissions([
            'dashboard.view',
            'students.view', 'students.create', 'students.edit', 'students.delete',
            'students.import', 'students.export',
            'results.view', 'results.enter', 'results.publish', 'results.export',
            'settings.manage', 'users.manage',
        ]);

        $director = Role::firstOrCreate(['name' => 'director', 'guard_name' => 'web']);
        $director->syncPermissions([
            'dashboard.view',
            'students.view',
            'results.view', 'results.publish', 'results.export',
        ]);

        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions(['dashboard.view']);
    }
}
