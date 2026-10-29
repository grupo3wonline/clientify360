<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Services\AccountService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            'lgpd_consent' => true,
            'lgpd_consent_at' => now(),
            'terms_accepted_at' => now(),
            'terms_version' => (string) config('legal.terms_version'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Por padrão, cada usuário criado vira dono da própria conta (tenant),
     * com os papéis provisionados — preserva o isolamento "uma conta por dono".
     *
     * O callback é registrado uma única vez (o Laravel só chama configure()
     * em Factory::new()). Para criar um usuário sem conta use withoutAccount(),
     * que descarta este callback via withoutAfterCreating().
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->account_id === null) {
                app(AccountService::class)->provisionForOwner($user);
            }
        });
    }

    /**
     * Usuário sem conta (ex.: para testar o fluxo de provisionamento/convite).
     *
     * Remove o afterCreating de provisionamento registrado em configure(),
     * garantindo que account_id permaneça nulo após create().
     */
    public function withoutAccount(): static
    {
        return $this->withoutAfterCreating();
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function withoutLgpdConsent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'lgpd_consent' => false,
            'lgpd_consent_at' => null,
        ]);
    }
}
