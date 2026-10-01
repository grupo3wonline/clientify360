<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
final class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            // Cria uma conta (via usuário dono) e vincula o cliente a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'document' => fake()->numerify('###.###.###-##'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->numerify('#####-###'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
