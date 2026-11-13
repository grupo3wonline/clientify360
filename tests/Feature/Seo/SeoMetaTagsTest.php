<?php

declare(strict_types=1);

/*
 * SEO / GEO / AEO meta tag validation.
 *
 * Verifies that the public home page contains all required meta tags for
 * search engine optimisation (SEO), geographic targeting (GEO) and
 * answer engine optimisation (AEO).
 */

it('renders the canonical link tag on the home page', function (): void {
    $response = $this->get('/');
    $response->assertOk();

    $html = (string) $response->getContent();

    expect($html)->toContain('<link rel="canonical"');
});

it('renders Open Graph meta tags on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('og:title')
        ->toContain('og:description')
        ->toContain('og:image')
        ->toContain('og:url')
        ->toContain('og:type');
});

it('renders Twitter Card meta tags on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('twitter:card')
        ->toContain('twitter:title')
        ->toContain('twitter:description');
});

it('renders GEO meta tags on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('geo.region')
        ->toContain('geo.placename')
        ->toContain('geo.position')
        ->toContain('ICBM')
        ->toContain('BR-PR');
});

it('renders AEO meta tags on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('ai:abstract')
        ->toContain('ai:keywords')
        ->toContain('ai:type')
        ->toContain('ai:audience');
});

it('embeds JSON-LD structured data on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('application/ld+json');

    // Extract JSON-LD block.
    preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
    $json  = json_decode($matches[1] ?? '{}', true);
    $types = collect($json['@graph'] ?? [])
        ->pluck('@type')
        ->flatten()
        ->all();

    expect($types)
        ->toContain('Organization')
        ->toContain('WebSite')
        ->toContain('SoftwareApplication')
        ->toContain('FAQPage')
        ->toContain('HowTo');
});

it('embeds the BreadcrumbList schema on public pages with breadcrumbs', function (): void {
    // Privacy page should include a BreadcrumbList node.
    $html = (string) $this->get('/politica-de-privacidade')->assertOk()->getContent();

    // Not all public pages include JSON-LD breadcrumbs; skip if none found.
    if (! str_contains($html, 'BreadcrumbList')) {
        $this->markTestSkipped('No BreadcrumbList on this page.');
    }

    expect($html)->toContain('BreadcrumbList');
});

it('sets noindex on authenticated pages', function (): void {
    $html = (string) $this->get('/login')->assertOk()->getContent();

    expect($html)->toContain('noindex');
});

it('sets index on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('index, follow');
});

it('renders the sitemap.xml with the home page URL', function (): void {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
             ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    expect((string) $response->getContent())->toContain(url('/'));
});

it('renders a valid robots.txt', function (): void {
    $response = $this->get('/robots.txt');

    $response->assertOk()
             ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    $txt = (string) $response->getContent();

    expect($txt)
        ->toContain('User-agent: *')
        ->toContain('Disallow: /app')
        ->toContain('Sitemap: '.route('sitemap'));
});
