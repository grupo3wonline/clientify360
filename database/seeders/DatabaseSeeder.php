<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user for testing
        User::factory()->create([
            'name' => 'Administrador',
            'username' => 'admin',
            'email' => 'admin@clientify360.test',
        ]);

        // Create additional test users
        User::factory(5)->create();

        // Seed clients for all users
        $this->call(ClientSeeder::class);
    }
}
