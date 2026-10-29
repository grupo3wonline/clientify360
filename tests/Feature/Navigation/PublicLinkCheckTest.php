<?php

declare(strict_types=1);

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

/*
 * Every link on the public, auth and error pages works: internal links answer
 * 200 (or one redirect to a 200), #anchors exist on the target page, external
 * links are https on known hosts and open safely, mailto is the 3W address.
 */

const LINK_HOST_ALLOWLIST = ['www.3wonline.com.br', 'wa.me', 'api.whatsapp.com', 'stripe.com', 'www.google.com', 'policies.google.com',
    'www.gov.br', 'gdpr.eu', 'oag.ca.gov', 'popia.co.za', 'inforegulator.org.za', 'www.inforegulator.org.za', 'www.anpd.gov.br', 'eur-lex.europa.eu', 'cppa.ca.gov', 'www.planalto.gov.br'];

/** @return array<string, string> page label => HTML */
function publicPagesHtml(): array
{
    Route::middleware('web')->get('/__links/error/{code}', fn (string $code) => abort((int) $code));
    config(['app.debug' => false]);

    $pages = [];
    foreach (['/', '/politica-de-privacidade', '/termos-de-uso', '/login', '/register', '/forgot-password'] as $url) {
        $pages[$url] = (string) test()->get($url)->assertOk()->getContent();
    }
    foreach ([401, 403, 404, 419, 429, 500, 503] as $code) {
        $pages["error {$code}"] = (string) test()->get("/__links/error/{$code}")->assertStatus($code)->getContent();
    }

    return $pages;
}

/** @return list<DOMElement> */
function anchorsOf(string $html): array
{
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
    libxml_clear_errors();

    return iterator_to_array($dom->getElementsByTagName('a'));
}

function pageHasId(string $html, string $id): bool
{
    return (bool) preg_match('/\sid=["\']'.preg_quote($id, '/').'["\']/', $html);
}

beforeEach(function (): void {
    $this->withoutMiddleware(ThrottleRequests::class);
});

it('resolves every internal link and anchor on the public pages', function (): void {
    $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
    $failures = [];
    $cache = [];
    $checked = 0;

    foreach (publicPagesHtml() as $page => $html) {
        foreach (anchorsOf($html) as $a) {
            $href = trim($a->getAttribute('href'));
            if ($href === '' || preg_match('#^(mailto:|tel:|javascript:)#', $href)) {
                continue;
            }
            $host = parse_url($href, PHP_URL_HOST);
            if ($host !== null && $host !== $appHost) {
                continue; // external, checked below
            }

            // "#x" points into the current page; "http://host#x" into the root.
            $current = str_starts_with($href, '#') && $page[0] === '/' ? $page : '/';
            $path = (string) (parse_url($href, PHP_URL_PATH) ?: $current);
            $query = parse_url($href, PHP_URL_QUERY);
            $fragment = parse_url($href, PHP_URL_FRAGMENT);
            $target = $path.($query ? '?'.$query : '');

            if (!isset($cache[$target])) {
                $response = test()->get($target);
                $status = $response->getStatusCode();
                if (in_array($status, [301, 302], true)) {
                    $location = (string) $response->headers->get('Location');
                    $fragment ??= parse_url($location, PHP_URL_FRAGMENT);
                    $response = test()->get((string) (parse_url($location, PHP_URL_PATH) ?: '/'));
                }
                $cache[$target] = [$status, $response->getStatusCode(), (string) $response->getContent()];
            }
            [$first, $final, $body] = $cache[$target];
            $checked++;

            if (!in_array($first, [200, 301, 302], true) || $final !== 200) {
                $failures[] = "{$page}: {$href} answered {$first}/{$final}";
            } elseif ($fragment !== null && $fragment !== '' && !pageHasId($body, $fragment)) {
                $failures[] = "{$page}: {$href} has no element with id=\"{$fragment}\"";
            }
        }
    }

    expect($failures)->toBe([])
        ->and($checked)->toBeGreaterThan(50);
});

it('keeps external links on https, on known hosts and with noopener', function (): void {
    $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
    $failures = [];

    foreach (publicPagesHtml() as $page => $html) {
        foreach (anchorsOf($html) as $a) {
            $href = $a->getAttribute('href');
            $host = parse_url($href, PHP_URL_HOST);
            if ($host === null || $host === $appHost || str_starts_with($href, 'mailto:')) {
                continue;
            }
            if (parse_url($href, PHP_URL_SCHEME) !== 'https') {
                $failures[] = "{$page}: {$href} is not https";
            }
            if (!in_array($host, LINK_HOST_ALLOWLIST, true)) {
                $failures[] = "{$page}: {$href} points to an unknown host";
            }
            if ($a->getAttribute('target') === '_blank' && !str_contains($a->getAttribute('rel'), 'noopener')) {
                $failures[] = "{$page}: {$href} opens a new tab without noopener";
            }
        }
    }

    expect($failures)->toBe([]);
});

it('uses only the 3W e-mail in mailto links', function (): void {
    $mailtos = collect(publicPagesHtml())
        ->flatMap(fn (string $html): array => array_map(fn (DOMElement $a): string => $a->getAttribute('href'), anchorsOf($html)))
        ->filter(fn (string $href): bool => str_starts_with($href, 'mailto:'))
        ->map(fn (string $href): string => strtok($href, '?'))
        ->unique()
        ->values();

    expect($mailtos->all())->toBe(['mailto:contato@3wonline.com.br']);
});
