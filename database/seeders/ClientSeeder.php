<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $users->each(function (User $user): void {
            Client::factory()
                ->count(10)
                ->create(['user_id' => $user->id]);
        });
    }
}
