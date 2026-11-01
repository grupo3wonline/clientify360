<?php

declare(strict_types=1);

use App\Models\User;
use Tests\Support\Personas;

/* AC-54, R-PRIV-6: public, versioned terms. */

it('shows the terms to a guest, read-only and indexable', function (): void {
    $this->get(route('terms.show'))
        ->assertOk()
        ->assertSee('Termos de Uso')
        ->assertSee('Planos e cobrança')
        ->assertSee('Curitiba/PR')
        ->assertSee('Versão '.config('legal.terms_version'))
        ->assertSee(route('privacy'))
        ->assertDontSee('name="accept_terms"', false)
        ->assertSee('<meta name="robots" content="index, follow', false);
});

it('shows the read-only page to a user who already accepted the current version', function (): void {
    $this->actingAs(Personas::owner())
        ->get(route('terms.show'))
        ->assertOk()
        ->assertDontSee('name="accept_terms"', false);
});

it('asks a user with an outdated acceptance to accept the current version', function (): void {
    $user = Personas::owner();
    $user->forceFill(['terms_version' => '1.0'])->save();

    $this->actingAs($user)->get(route('profile.edit'))->assertRedirect(route('terms.show'));
    $this->get(route('terms.show'))
        ->assertOk()
        ->assertSee('name="accept_terms"', false)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('records the current terms version on acceptance', function (): void {
    $user = User::factory()->create(['terms_accepted_at' => null, 'terms_version' => null]);

    $this->actingAs($user)
        ->post(route('terms.accept'), ['accept_terms' => '1'])
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->terms_version)->toBe(config('legal.terms_version'))
        ->and($user->fresh()->hasAcceptedCurrentTerms())->toBeTrue();
    $this->get(route('profile.edit'))->assertOk();
});

it('requires authentication to accept the terms', function (): void {
    $this->post(route('terms.accept'), ['accept_terms' => '1'])->assertRedirect(route('login'));
});
