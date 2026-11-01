<?php

declare(strict_types=1);

use App\Models\User;

function loginPayload(User $user, array $overrides = []): array
{
    return array_merge([
        'username' => $user->username,
        'email' => $user->email,
        'password' => 'password',
    ], $overrides);
}

it('renders the username, email and password inputs as required and autocomplete off', function (): void {
    $html = $this->get('/login')->assertOk()->getContent();

    foreach (['username', 'email', 'password'] as $field) {
        expect($html)->toMatch('/<input[^>]*name="'.$field.'"[^>]*required[^>]*autocomplete="off"/s');
    }
});

it('authenticates with the correct username, email and password', function (): void {
    $user = User::factory()->create();

    $this->post('/login', loginPayload($user))->assertRedirect('/app');

    $this->assertAuthenticatedAs($user);
});

it('accepts the e-mail in any letter case', function (): void {
    $user = User::factory()->create(['email' => 'maria@example.com']);

    $this->post('/login', loginPayload($user, ['email' => '  MARIA@Example.com ']))->assertRedirect('/app');

    $this->assertAuthenticatedAs($user);
});

it('rejects credentials that do not belong to the same user', function (callable $payload): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $this->post('/login', $payload($user, $other))
        ->assertSessionHasErrors(['email' => 'Essas credenciais não correspondem aos nossos registros.']);

    $this->assertGuest();
})->with([
    'right username + other user email' => [fn (User $u, User $o): array => loginPayload($u, ['email' => $o->email])],
    'right email + other user username' => [fn (User $u, User $o): array => loginPayload($u, ['username' => $o->username])],
    'unknown username' => [fn (User $u, User $o): array => loginPayload($u, ['username' => 'nao_existe'])],
    'unknown email' => [fn (User $u, User $o): array => loginPayload($u, ['email' => 'nao-existe@example.com'])],
    'right pair + wrong password' => [fn (User $u, User $o): array => loginPayload($u, ['password' => 'wrong-password'])],
]);

it('requires the username', function (): void {
    $user = User::factory()->create();

    $this->post('/login', loginPayload($user, ['username' => '']))
        ->assertSessionHasErrors(['username' => 'Informe seu nome de usuário.']);

    $this->assertGuest();
});

it('requires the email', function (): void {
    $user = User::factory()->create();

    $this->post('/login', loginPayload($user, ['email' => '']))
        ->assertSessionHasErrors(['email' => 'Informe seu e-mail.']);

    $this->assertGuest();
});

it('locks the credential tuple after five failed attempts', function (): void {
    $user = User::factory()->create();
    $payload = loginPayload($user, ['password' => 'wrong-password']);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', $payload);
    }

    $response = $this->post('/login', loginPayload($user));

    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Muitas tentativas de login');
    $this->assertGuest();
});

it('returns 429 on the 21st login POST from one IP within a minute', function (): void {
    for ($i = 1; $i <= 20; $i++) {
        $this->post('/login', [
            'username' => "user{$i}",
            'email' => "user{$i}@example.com",
            'password' => 'wrong-password',
        ])->assertStatus(302);
    }

    $this->post('/login', [
        'username' => 'user21',
        'email' => 'user21@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(429);
});
