<?php

declare(strict_types=1);

use Pest\Stressless\Factory;

use function Pest\Stressless\stress;

use Tests\Load\Support\Preflight;

/*
 * Load and stress scenarios (k6 through pest-plugin-stressless), run only by
 * `composer test:load` against the local `php artisan serve` on the disposable
 * SQLite database. `php artisan serve` on Windows is single-process, so the
 * thresholds are lenient and the numbers are informative.
 */

function printLoadReport(string $scenario, Factory $result): void
{
    $duration = $result->requests->duration;
    fwrite(STDERR, sprintf(
        "\n[load] %-10s vus=%d requests=%d p95=%.1fms med=%.1fms failed=%.2f%%\n",
        $scenario,
        $result->testRun->concurrency,
        $result->requests->count,
        $duration->p95,
        $duration->med,
        $result->requests->failed->rate * 100,
    ));
}

it('refuses a non-local target before any request', function (): void {
    expect(fn () => Preflight::assertLocal('https://clientify360.example.com'))->toThrow(RuntimeException::class, 'Load tests run only against the local server.')
        ->and(fn () => Preflight::assertLocal('http://10.0.0.5:8000'))->toThrow(RuntimeException::class);
});

it('serves the landing page under load', function (): void {
    $result = stress(Preflight::url('/'))->concurrently(10)->for(20)->seconds();
    printLoadReport('landing', $result);

    expect($result->requests->duration->p95)->toBeLessThan(1500.0)
        ->and($result->requests->failed->rate)->toBeLessThan(0.01);
});

it('serves the login page under load', function (): void {
    $result = stress(Preflight::url('/login'))->concurrently(10)->for(20)->seconds();
    printLoadReport('login', $result);

    expect($result->requests->duration->p95)->toBeLessThan(1500.0)
        ->and($result->requests->failed->rate)->toBeLessThan(0.01);
});

it('serves the authenticated dashboard under load', function (): void {
    $result = stress(Preflight::url('/app'))->headers(['Cookie' => Preflight::ensure()['cookie']])
        ->concurrently(5)->for(20)->seconds();
    printLoadReport('dashboard', $result);

    expect($result->requests->duration->p95)->toBeLessThan(3000.0)
        ->and($result->requests->failed->rate)->toBeLessThan(0.01);
});

it('serves the authenticated client listing (Blade) under load', function (): void {
    $result = stress(Preflight::url('/clients'))->headers(['Cookie' => Preflight::ensure()['cookie']])
        ->concurrently(5)->for(20)->seconds();
    printLoadReport('clients', $result);

    expect($result->requests->duration->p95)->toBeLessThan(3000.0)
        ->and($result->requests->failed->rate)->toBeLessThan(0.01);
});

it('serves the API client listing under load', function (): void {
    $result = stress(Preflight::url('/api/v1/clients?per_page=15'))
        ->headers(['Authorization' => 'Bearer '.Preflight::ensure()['token'], 'Accept' => 'application/json'])
        ->concurrently(10)->for(20)->seconds();
    printLoadReport('api', $result);

    expect($result->requests->duration->p95)->toBeLessThan(1500.0)
        ->and($result->requests->failed->rate)->toBeLessThan(0.01);
});

it('reports how the landing degrades under a spike', function (): void {
    $result = stress(Preflight::url('/'))->concurrently(30)->for(15)->seconds();
    printLoadReport('spike', $result);

    // Reported only: shows degradation of the single-process dev server.
    expect($result->requests->count)->toBeGreaterThan(0);
});
