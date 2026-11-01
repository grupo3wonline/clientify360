<?php

declare(strict_types=1);

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Support\Personas;

/* AC-55, AC-67: error pages with layout, footer, CTA, home link, pt-BR text and no consent/analytics. */

beforeEach(function (): void {
    config(['app.debug' => false]);

    Route::middleware('web')->get('/__error/{code}', function (string $code): never {
        match ($code) {
            '429' => throw new ThrottleRequestsException('Too Many Attempts.', null, ['Retry-After' => 42]),
            '500' => throw new RuntimeException('boom'),
            default => abort((int) $code),
        };
    });
});

function assertErrorPage(string $html, string $code): void
{
    expect($html)->toContain('>'.$code.'<')
        ->toContain('Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL')
        ->toContain('Voltar para a página inicial')
        ->toContain('https://www.3wonline.com.br/?utm_source=clientify360')
        ->toContain('utm_content=error-'.$code)
        ->toContain('href="'.url('/politica-de-privacidade').'#cookies"')
        ->toContain('href="'.url('/politica-de-privacidade').'#nao-vender"')
        ->toContain('noindex')
        ->not->toContain('__consentConfig')
        ->not->toContain('googletagmanager')
        ->not->toContain('cookieConsent(')
        ->not->toContain('csrf-token');
}

it('renders every error page with the shared error layout', function (string $code, string $heading): void {
    $response = $this->get('/__error/'.$code);

    $response->assertStatus((int) $code);
    $html = (string) $response->getContent();
    assertErrorPage($html, $code);
    expect($html)->toContain($heading);
})->with([
    ['401', 'Acesso não autenticado'],
    ['403', 'Acesso negado'],
    ['404', 'Página não encontrada'],
    ['419', 'Sessão expirada'],
    ['429', 'Muitas requisições'],
    ['500', 'Erro interno'],
    ['503', 'Em manutenção'],
]);

it('renders the 404 page for unknown URLs with a contact link', function (): void {
    $html = (string) $this->get('/pagina-que-nao-existe')->assertNotFound()->getContent();

    assertErrorPage($html, '404');
    expect($html)->toContain('href="'.url('/#contato').'"');
});

it('shows the Retry-After hint on 429', function (): void {
    expect((string) $this->get('/__error/429')->getContent())->toContain('Aguarde 42 segundos e tente novamente.');
});

it('renders 500 and 503 with the database unavailable', function (string $view): void {
    // Point the default connection to a database that does not exist (the in-memory test DB stays intact).
    $previous = config('database.default');
    config([
        'database.connections.unavailable' => ['driver' => 'sqlite', 'database' => '/nonexistent/x.sqlite', 'prefix' => ''],
        'database.default' => 'unavailable',
    ]);
    DB::purge('unavailable');

    try {
        $html = view($view)->render();
    } finally {
        config(['database.default' => $previous]);
    }

    expect($html)->toContain('Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL')
        ->toContain('Voltar para a página inicial');
})->with(['errors.500', 'errors.503']);

it('translates the permission denial for a collaborator to pt-BR', function (): void {
    $owner = Personas::owner();
    $collaborator = Personas::collaborator($owner->account);
    $this->actingAs($collaborator);

    $html = (string) $this->get(route('finance.index'))->assertForbidden()->getContent();

    expect($html)->toContain('Você não tem permissão')
        ->not->toContain('User does not have')
        ->not->toContain('This action is unauthorized')
        ->not->toContain('unauthorized');
});

it('offers the Profissional plan to a Básico owner hitting a plan feature', function (): void {
    $this->actingAs(Personas::owner());

    $html = (string) $this->get(route('export.clients'))->assertForbidden()->getContent();

    expect($html)->toContain('Conhecer o plano Profissional')
        ->toContain(route('billing.index'));
});
