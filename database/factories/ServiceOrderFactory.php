<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\Quote;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOrder>
 */
final class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        return [
            // Cria uma conta (via usuário dono) e vincula a ordem de serviço a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'number' => 'OS-'.now()->format('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => ServiceOrder::STATUS_OPEN,
            'opened_at' => now(),
            'total' => fake()->randomFloat(2, 100, 5000),
        ];
    }

    /** Vincula a ordem de serviço a uma conta já existente. */
    public function forAccount(string $accountId): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $accountId,
        ]);
    }

    /** Vincula a ordem de serviço a um cliente (reaproveita a conta do cliente). */
    public function forClient(Client $client): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $client->account_id,
            'client_id' => $client->id,
        ]);
    }

    /** Vincula a ordem de serviço a um orçamento (reaproveita a conta e o cliente). */
    public function forQuote(Quote $quote): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $quote->account_id,
            'client_id' => $quote->client_id,
            'quote_id' => $quote->id,
        ]);
    }
}
