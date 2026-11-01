<?php

declare(strict_types=1);

use Tests\Support\Personas;

/* AC-48, AC-49, R-SEO-1 to R-SEO-5. */

/** @return array<string, mixed> the decoded JSON-LD document of the page */
function jsonLd(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
    expect($matches[1])->toHaveCount(1);

    return json_decode($matches[1][0], true, flags: JSON_THROW_ON_ERROR);
}

/** @return list<string> the @type of every node in the graph */
function graphTypes(array $document): array
{
    return array_column($document['@graph'], '@type');
}

function metaContent(string $html, string $attribute, string $name): ?string
{
    return preg_match('#<meta '.$attribute.'="'.preg_quote($name, '#').'" content="([^"]*)"#', $html, $m) ? html_entity_decode($m[1]) : null;
}

it('puts Organization, WebSite, SoftwareApplication, FAQPage and BreadcrumbList in the landing graph', function (): void {
    $html = (string) $this->get(route('home'))->assertOk()->getContent();
    $document = jsonLd($html);
    $nodes = collect($document['@graph'])->keyBy('@type');

    expect($document['@context'])->toBe('https://schema.org')
        ->and(graphTypes($document))->toEqualCanonicalizing(['Organization', 'WebSite', 'SoftwareApplication', 'FAQPage', 'BreadcrumbList'])
        ->and($nodes['Organization']['name'])->toBe('Grupo 3W Online')
        ->and(array_column($nodes['SoftwareApplication']['offers'], 'price'))->toBe(['0', '29.90'])
        ->and($nodes['SoftwareApplication']['offers'][1]['priceCurrency'])->toBe('BRL')
        ->and($nodes['FAQPage']['mainEntity'])->toHaveCount(substr_count($html, 'id="faq-q-'))
        ->and($nodes['BreadcrumbList']['itemListElement'][0]['name'])->toBe('Início');

    // The visible FAQ and the FAQPage have the same questions.
    foreach ($nodes['FAQPage']['mainEntity'] as $question) {
        expect($html)->toContain(e($question['name']));
    }
});

it('gives privacy and terms a WebPage and a BreadcrumbList', function (string $route, string $name): void {
    $document = jsonLd((string) $this->get(route($route))->assertOk()->getContent());

    expect(graphTypes($document))->toEqualCanonicalizing(['WebPage', 'BreadcrumbList'])
        ->and($document['@graph'][1]['itemListElement'][1]['name'])->toBe($name);
})->with([
    ['privacy', 'Política de Privacidade'],
    ['terms.show', 'Termos de Uso'],
]);

it('has a unique title and description, canonical, hreflang, OG and Twitter tags on every public page', function (): void {
    $titles = [];
    $descriptions = [];

    foreach (['home', 'privacy', 'terms.show'] as $route) {
        $url = route($route);
        $html = (string) $this->get($url.'?utm_source=teste')->assertOk()->getContent();
        $canonical = $route === 'home' ? url('/') : $url;

        preg_match('#<title>(.*?)</title>#s', $html, $title);
        $titles[] = $title[1];
        $descriptions[] = metaContent($html, 'name', 'description');

        expect($html)->toContain('<link rel="canonical" href="'.$canonical.'">')
            ->toContain('<link rel="alternate" hreflang="pt-BR" href="'.$canonical.'">')
            ->toContain('<link rel="alternate" hreflang="x-default" href="'.$canonical.'">')
            ->toContain('<meta name="robots" content="index, follow')
            ->not->toContain('name="keywords"')
            ->and(metaContent($html, 'property', 'og:locale'))->toBe('pt_BR')
            ->and(metaContent($html, 'property', 'og:image'))->toBe(asset('images/og-image.png'))
            ->and(metaContent($html, 'property', 'og:image:width'))->toBe('1200')
            ->and(metaContent($html, 'property', 'og:image:height'))->toBe('630')
            ->and(metaContent($html, 'property', 'og:url'))->toBe($canonical)
            ->and(metaContent($html, 'name', 'twitter:card'))->toBe('summary_large_image')
            ->and(metaContent($html, 'name', 'theme-color'))->toBe(config('brand.colors.brand.600'))
            ->and(substr_count($html, '<h1'))->toBe(1);
    }

    expect(array_unique($titles))->toHaveCount(3)
        ->and(array_unique($descriptions))->toHaveCount(3)
        ->and(html_entity_decode($titles[0]))->toBe('Clientify360 — CRM com IA para pequenos negócios | R$ 29,90/mês')
        ->and(html_entity_decode($titles[1]))->toBe('Política de Privacidade | Clientify360')
        ->and(html_entity_decode($titles[2]))->toBe('Termos de Uso | Clientify360');
    expect(file_exists(public_path('images/og-image.png')))->toBeTrue();
});

it('gives every image an alt text and preloads the font on public pages', function (): void {
    foreach (['home', 'privacy', 'terms.show'] as $route) {
        $html = (string) $this->get(route($route))->getContent();

        preg_match_all('#<img\b[^>]*>#i', $html, $images);
        expect($images[0])->not->toBeEmpty();
        foreach ($images[0] as $img) {
            expect($img)->toMatch('#\balt="[^"]+"#');
        }
        expect($html)->toContain('rel="preload" as="font" type="font/woff2"');
    }
});

it('sends noindex and no JSON-LD on auth, app and error pages', function (): void {
    $pages = [
        (string) $this->get(route('login'))->getContent(),
        (string) $this->get(route('register'))->getContent(),
        (string) $this->get('/pagina-que-nao-existe')->assertNotFound()->getContent(),
    ];

    $this->actingAs(Personas::owner());
    $pages[] = (string) $this->get(route('profile.edit'))->assertOk()->getContent();
    $pages[] = (string) $this->get('/app')->assertOk()->getContent();

    foreach ($pages as $html) {
        expect($html)->toContain('<meta name="robots" content="noindex, nofollow">')
            ->not->toContain('application/ld+json');
    }
});

it('lists the three public URLs in the sitemap with an honest lastmod', function (): void {
    $response = $this->get(route('sitemap'))->assertOk();
    $xml = simplexml_load_string((string) $response->getContent());
    $locs = array_map('strval', $xml->xpath('//*[local-name()="loc"]'));

    expect($response->headers->get('Content-Type'))->toContain('application/xml')
        ->and($locs)->toBe([route('home'), route('privacy'), route('terms.show')]);

    $lastmod = (string) $xml->url[1]->lastmod;
    expect(strtotime($lastmod))->toBe(filemtime(resource_path('views/pages/privacy.blade.php')));
});

it('disallows the private areas in robots.txt but keeps the auth pages crawlable', function (): void {
    $response = $this->get(route('robots'))->assertOk();
    $body = (string) $response->getContent();

    expect($response->headers->get('Content-Type'))->toContain('text/plain')
        ->and($body)->toContain("Disallow: /app\n")
        ->toContain("Disallow: /api\n")
        ->toContain("Disallow: /dashboard\n")
        ->toContain("Disallow: /f/\n")
        ->toContain('Allow: /llms.txt')
        ->toContain('Sitemap: '.route('sitemap'))
        ->not->toContain('Disallow: /login')
        ->not->toContain('Disallow: /register')
        ->not->toContain('Disallow: /termos-de-uso')
        ->not->toContain('Disallow: /politica-de-privacidade');
});

it('serves /llms.txt as plain text with the product, plans, FAQ and developer', function (): void {
    $response = $this->get(route('llms'))->assertOk();
    $body = (string) $response->getContent();

    expect($response->headers->get('Content-Type'))->toBe('text/plain; charset=UTF-8')
        ->and($body)->toStartWith('# Clientify360')
        ->toContain('## Planos')
        ->toContain('R$ 29,90 por mês')
        ->toContain('## Perguntas frequentes')
        ->toContain('### O que é o Clientify360?')
        ->toContain('Grupo 3W Online')
        ->toContain('contato@3wonline.com.br')
        ->toContain(route('privacy'))
        ->not->toContain('&amp;')
        ->not->toContain('<');
});
