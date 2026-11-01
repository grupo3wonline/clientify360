<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\LeadForm;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Support\Personas;

/*
 * Server-side contracts of the shared layouts (AC-38 to AC-43, AC-45):
 * footer text, font preload, theme script before CSS, no external fonts,
 * autocomplete="off", one loading overlay, the 3W CTA and breadcrumbs.
 */

const FOOTER_TEXT = 'Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL';

function guardDom(TestResponse $response): DOMDocument
{
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8"?>'.$response->getContent());
    libxml_clear_errors();

    return $dom;
}

function assertSharedLayout(TestResponse $response, string $page): void
{
    $html = (string) $response->getContent();
    $dom = guardDom($response);

    expect($html)->toContain(FOOTER_TEXT)
        ->and($html)->toMatch('#<link rel="preload" as="font" type="font/woff2" crossorigin href="[^"]*inter-latin-wght-normal[^"]*\.woff2">#')
        ->and($html)->not->toContain('fonts.googleapis')
        ->and($html)->not->toContain('fonts.gstatic')
        ->and($html)->not->toContain('fonts.bunny');

    // Theme script runs before the first stylesheet (no flash of the wrong theme).
    $themePos = strpos($html, 'filament:theme:mode');
    $cssPos = strpos($html, 'rel="stylesheet"');
    expect($themePos)->not->toBeFalse("{$page}: theme script missing")
        ->and($cssPos)->not->toBeFalse("{$page}: stylesheet missing")
        ->and($themePos < $cssPos)->toBeTrue("{$page}: theme script must come before the stylesheet");

    // autocomplete="off" on every form and field.
    foreach (['form', 'input', 'textarea', 'select'] as $tag) {
        foreach ($dom->getElementsByTagName($tag) as $node) {
            /** @var DOMElement $node */
            expect($node->getAttribute('autocomplete'))->toBe('off', "{$page}: <{$tag} name=\"{$node->getAttribute('name')}\"> without autocomplete=\"off\"");
        }
    }

    // One loading overlay, hidden by default.
    expect(substr_count($html, 'id="loading-overlay"'))->toBe(1, "{$page}: overlay must be included once");
    $overlay = $dom->getElementById('loading-overlay');
    expect($overlay?->hasAttribute('hidden'))->toBeTrue("{$page}: overlay must start hidden");

    // 3W CTA with UTM, new tab and noopener.
    $cta = collect(iterator_to_array($dom->getElementsByTagName('a')))
        ->first(fn (DOMElement $a): bool => str_starts_with($a->getAttribute('href'), 'https://www.3wonline.com.br/?utm_source=clientify360'));
    expect($cta)->not->toBeNull("{$page}: 3W CTA missing")
        ->and($cta->getAttribute('target'))->toBe('_blank')
        ->and($cta->getAttribute('rel'))->toContain('noopener');
}

function assertSingleH1(TestResponse $response, string $page): void
{
    expect(guardDom($response)->getElementsByTagName('h1')->length)->toBe(1, "{$page}: exactly one <h1> expected");
}

it('renders the shared layout contracts on public pages', function (string $url): void {
    $response = $this->get($url)->assertOk();
    assertSharedLayout($response, $url);
    assertSingleH1($response, $url);
})->with(['/', '/politica-de-privacidade']);

it('renders the shared layout contracts on auth pages', function (string $url): void {
    $response = $this->get($url)->assertOk();
    assertSharedLayout($response, $url);
    assertSingleH1($response, $url);
    expect((string) $response->getContent())->toContain('noindex');
})->with(['/login', '/register', '/forgot-password']);

it('renders the shared layout, breadcrumb and one h1 on every Blade app module page', function (string $route): void {
    $owner = Personas::owner();
    $this->actingAs($owner);

    $response = $this->get(route($route))->assertOk();

    assertSharedLayout($response, $route);
    assertSingleH1($response, $route);
    expect((string) $response->getContent())
        ->toContain('<nav aria-label="Trilha de navegação"')
        ->toContain('noindex, nofollow');
})->with([
    'clients.index', 'clients.create', 'reminders.index', 'appointments.index', 'appointments.create',
    'interactions.index', 'tags.index', 'deals.index', 'deals.list', 'stages.index', 'activities.index',
    'quotes.index', 'quotes.create', 'service-orders.index', 'service-orders.create', 'products.index', 'products.create',
    'purchases.index', 'purchases.create', 'lead-forms.index', 'lead-forms.create', 'finance.index', 'finance.entries',
    'finance.categories', 'reports.index', 'notifications.index', 'ai.settings', 'profile.edit', 'team.index',
    'billing.index', 'billing.invoices',
]);

it('renders the shared layout on a client detail page', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);
    $client = Client::factory()->create();

    $response = $this->get(route('clients.show', $client))->assertOk();

    assertSharedLayout($response, 'clients.show');
    assertSingleH1($response, 'clients.show');
});

it('renders header, footer, 3W CTA and autocomplete="off" on the public lead form', function (): void {
    $this->actingAs(Personas::owner());
    $form = LeadForm::create([
        'name' => 'Bio', 'slug' => 'bio-guard1', 'title' => 'Fale conosco',
        'fields' => [['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'required' => true]],
    ]);
    auth()->logout();

    $response = $this->get('/f/'.$form->slug)->assertOk();
    $html = (string) $response->getContent();
    $dom = guardDom($response);

    expect($html)->toContain(FOOTER_TEXT)
        ->and($dom->getElementsByTagName('header')->length)->toBe(1)
        ->and($dom->getElementsByTagName('footer')->length)->toBe(1);

    foreach (['form', 'input', 'textarea', 'select'] as $tag) {
        foreach ($dom->getElementsByTagName($tag) as $node) {
            /** @var DOMElement $node */
            expect($node->getAttribute('autocomplete'))->toBe('off', "/f: <{$tag} name=\"{$node->getAttribute('name')}\"> without autocomplete=\"off\"");
        }
    }

    $cta = collect(iterator_to_array($dom->getElementsByTagName('a')))
        ->first(fn (DOMElement $a): bool => str_starts_with($a->getAttribute('href'), 'https://www.3wonline.com.br/?utm_source=clientify360'));
    expect($cta)->not->toBeNull('/f: 3W CTA missing')
        ->and($cta->getAttribute('href'))->toContain('utm_content=lead-form')
        ->and($cta->getAttribute('target'))->toBe('_blank')
        ->and($cta->getAttribute('rel'))->toContain('noopener');
});

it('gives every image an alt text and never shows the old 39,90 price', function (): void {
    $owner = Personas::owner();
    $urls = ['/', '/politica-de-privacidade', '/termos-de-uso', '/login', '/register', '/forgot-password'];
    $appUrls = [route('clients.index'), route('products.index'), route('quotes.index'), route('billing.index'), route('profile.edit'), '/app', '/app/clients'];
    $failures = [];

    $check = function (string $url) use (&$failures): void {
        $response = $this->get($url)->assertOk();
        foreach (guardDom($response)->getElementsByTagName('img') as $img) {
            /** @var DOMElement $img */
            if (!$img->hasAttribute('alt')) {
                $failures[] = "{$url}: <img src=\"{$img->getAttribute('src')}\"> without alt";
            }
        }
        if (str_contains((string) $response->getContent(), '39,90')) {
            $failures[] = "{$url}: shows 39,90";
        }
    };

    array_map($check, $urls);
    $this->actingAs($owner);
    array_map($check, $appUrls);

    expect($failures)->toBe([]);
});

it('renders the shared layout contracts on the terms page', function (): void {
    $response = $this->get('/termos-de-uso')->assertOk();

    assertSharedLayout($response, '/termos-de-uso');
    assertSingleH1($response, '/termos-de-uso');
});

it('marks download links so the loading overlay ignores them', function (): void {
    /** @var User $owner */
    $owner = Personas::owner('professional');
    $this->actingAs($owner);

    $html = (string) $this->get(route('clients.index'))->assertOk()->getContent();
    expect($html)->toMatch('#<a href="[^"]*/export/clients"[^>]*data-no-loading#');

    $profile = (string) $this->get(route('profile.edit'))->assertOk()->getContent();
    expect($profile)->toMatch('#href="[^"]*/profile/exportar-dados"[^>]*data-no-loading|data-no-loading[^>]*href="[^"]*/profile/exportar-dados"#s');
});

it('ships the overlay module with the bfcache, failsafe and skip rules', function (): void {
    $js = (string) file_get_contents(resource_path('js/loading-overlay.js'));

    expect($js)->toContain("addEventListener('pageshow', hide)")
        ->toContain('15000')
        ->toContain('data-no-loading')
        ->toContain("hasAttribute('download')")
        ->toContain('mailto:|tel:')
        ->toContain('interceptRequest')
        ->toContain('onFinish(done)')
        ->toContain('onCancel(done)')
        ->toContain("addEventListener('abort', done")
        ->toContain("=== 'poll'");
});

it('keeps countdown markup out of the 3W CTA', function (): void {
    $cta = (string) file_get_contents(resource_path('views/components/cta-3w.blade.php'));

    expect($cta)->not->toContain('countdown')->not->toContain('setInterval');
});
