<?php

declare(strict_types=1);

use App\Support\Cta;
use Illuminate\Support\Carbon;

it('builds the 3W link with UTM parameters and the placement', function (): void {
    $url = Cta::url('landing-hero');

    expect($url)->toStartWith('https://www.3wonline.com.br/?')
        ->and($url)->toContain('utm_source=clientify360')
        ->and($url)->toContain('utm_medium=referral')
        ->and($url)->toContain('utm_campaign=cta-grupo-3w')
        ->and($url)->toContain('utm_content=landing-hero');
});

it('fills the urgency line with the current month in pt-BR', function (): void {
    Carbon::setTestNow('2026-05-10 12:00:00');

    expect(Cta::urgency())->toBe('Garanta seu lugar na agenda de maio.');

    Carbon::setTestNow();
});
