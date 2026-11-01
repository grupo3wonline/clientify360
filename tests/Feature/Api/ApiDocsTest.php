<?php

declare(strict_types=1);

use Tests\Support\Personas;

it('sends guests to the login page', function (): void {
    $this->get('/api/documentation')->assertRedirect(route('login'));
    $this->get('/docs')->assertRedirect(route('login'));
});

it('forbids the docs on the Básico plan', function (): void {
    $this->actingAs(Personas::owner('basic'))->get('/api/documentation')->assertForbidden();
});

it('serves the docs to a Profissional account', function (): void {
    $this->actingAs(Personas::owner('professional'))->get('/api/documentation')->assertOk();
    $this->actingAs(Personas::owner('professional'))->get('/docs')->assertOk()->assertJsonPath('info.title', 'Clientify360 API');
});
