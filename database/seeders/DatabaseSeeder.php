<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = \App\Models\User::factory()->create([
            'name' => 'NOC Administrator',
            'email' => 'admin@netio.local',
            'password' => 'password',
        ]);

        $admin->assignRole('Admin');
    }
}
