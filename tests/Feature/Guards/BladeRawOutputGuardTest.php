<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/*
 * Unescaped Blade output ({!! !!}) is allowed only where the value is trusted
 * (config, static markup or JSON encoded with JSON_HEX_TAG). A new {!! fails
 * here until it is reviewed and added with a reason.
 */

const RAW_OUTPUT_ALLOWLIST = [
    // JSON-LD from StructuredData::encode() (JSON_HEX_TAG), built from config and static strings.
    'components/seo.blade.php' => 1,
    // Consent config from config/services, json_encode with JSON_HEX_TAG | JSON_HEX_AMP.
    'components/analytics.blade.php' => 1,
    // Static icon markup chosen by a boolean; no user content.
    'lead-forms/show.blade.php' => 1,
    // text/plain response: plans from config/plans.php, FAQ from static arrays, route URLs.
    'pages/llms.blade.php' => 10,
];

it('uses unescaped output only in the reviewed views', function (): void {
    $found = [];
    foreach ((new Finder)->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
        $count = substr_count($file->getContents(), '{!!');
        if ($count > 0) {
            $found[str_replace('\\', '/', $file->getRelativePathname())] = $count;
        }
    }
    ksort($found);
    $expected = RAW_OUTPUT_ALLOWLIST;
    ksort($expected);

    expect($found)->toBe($expected);
});
