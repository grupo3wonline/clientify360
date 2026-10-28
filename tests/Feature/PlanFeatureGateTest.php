<?php

declare(strict_types=1);

use App\Models\Client;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\Personas;

it('blocks CSV and XLSX export on Básico with the pt-BR message', function (): void {
    $this->actingAs(Personas::owner());

    $this->get(route('export.clients'))->assertForbidden();
    $this->get(route('export.clients.xlsx'))->assertForbidden();

    $this->getJson(route('export.clients'))
        ->assertForbidden()
        ->assertExactJson(['message' => 'Exportação disponível apenas no plano Profissional.']);
});

it('aborts HTML requests with the feature message', function (): void {
    $this->actingAs(Personas::owner());
    $this->withoutExceptionHandling();

    try {
        $this->get(route('export.clients'));
        $this->fail('Expected a 403.');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403)
            ->and($e->getMessage())->toBe('Exportação disponível apenas no plano Profissional.');
    }
});

it('lets Profissional export clients', function (): void {
    $owner = Personas::owner('professional');
    $this->actingAs($owner);
    Client::create(['name' => 'Cliente Pro', 'email' => 'pro@ex.com']);

    $this->get(route('export.clients'))->assertOk();
});

it('returns the feature message as JSON for PDF and API gates', function (string $feature, string $message): void {
    Route::middleware(['web', 'auth', "plan.feature:{$feature}"])->get("/_gate/{$feature}", fn () => 'ok');

    $this->actingAs(Personas::owner());

    $this->getJson("/_gate/{$feature}")->assertForbidden()->assertExactJson(['message' => $message]);
})->with([
    'pdf' => ['pdf', 'PDF disponível apenas no plano Profissional.'],
    'api' => ['api', 'Acesso à API disponível apenas no plano Profissional.'],
]);

it('passes the gate on Profissional', function (): void {
    Route::middleware(['web', 'auth', 'plan.feature:pdf'])->get('/_gate/pdf-pro', fn () => 'ok');

    $this->actingAs(Personas::owner('professional'));

    $this->get('/_gate/pdf-pro')->assertOk()->assertSee('ok');
});

it('keeps the AI redirect with flash on Básico', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);
    $client = Client::create(['name' => 'Cliente IA', 'email' => 'ia@ex.com']);

    $this->from(route('clients.show', $client))
        ->post(route('ai.analyze', $client))
        ->assertRedirect(route('clients.show', $client))
        ->assertSessionHas('error', 'Recurso disponível apenas no plano Profissional.');

    $this->post(route('ai.saveSettings'), ['ai_preferred_provider' => 'gemini'])
        ->assertRedirect(route('ai.settings'))
        ->assertSessionHas('error', 'Recurso disponível apenas no plano Profissional.');
});
