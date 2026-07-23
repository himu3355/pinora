<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ShieldSeeder::class,
            RolesAndPermissionsSeeder::class,
            DummyDataSeeder::class,
        ]);

        // Assign super_admin role to test@example.com user if they exist or create one
        $user = User::firstOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('super_admin');
    }
}
