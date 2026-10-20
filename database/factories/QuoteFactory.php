<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
final class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);

        return [
            // Cria uma conta (via usuário dono) e vincula o orçamento a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'number' => fake()->unique()->numerify('ORC-####'),
            'title' => fake()->sentence(3),
            'status' => 'draft',
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
        ];
    }

    /** Vincula o orçamento a uma conta já existente. */
    public function forAccount(string $accountId): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $accountId,
        ]);
    }

    /** Vincula o orçamento a um cliente (reaproveita a conta do cliente). */
    public function forClient(Client $client): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $client->account_id,
            'client_id' => $client->id,
        ]);
    }
}
