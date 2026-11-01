<?php

declare(strict_types=1);

use App\Models\User;

it('has no Filament login page', function (): void {
    $this->get('/app/login')->assertNotFound();
});

it('redirects guests from the panel to the Breeze login', function (string $url): void {
    $this->get($url)->assertRedirect('/login');
})->with(['/app', '/app/clients']);

it('redirects unverified users to the e-mail verification notice', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get('/app')->assertRedirect('/verify-email');
});

it('redirects to the home page after the Filament logout', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('filament.app.auth.logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});
