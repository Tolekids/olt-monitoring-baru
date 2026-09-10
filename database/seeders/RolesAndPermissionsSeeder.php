<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'devices.view',
            'devices.manage',
            'syslog.view',
            'cli.open',
            'onu.control',
            'audit.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $admin = Role::findOrCreate('Admin', 'web');
        $noc = Role::findOrCreate('NOC', 'web');
        $fieldTechnician = Role::findOrCreate('Teknisi Field', 'web');

        $admin->syncPermissions($permissions);
        $noc->syncPermissions([
            'dashboard.view',
            'devices.view',
            'syslog.view',
            'cli.open',
            'onu.control',
            'audit.view',
        ]);
        $fieldTechnician->syncPermissions([
            'dashboard.view',
            'devices.view',
            'syslog.view',
            'cli.open',
        ]);
    }
}
