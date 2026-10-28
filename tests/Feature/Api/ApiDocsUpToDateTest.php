<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('has a committed OpenAPI file that matches the annotations', function (): void {
    $tmp = storage_path('framework/testing/api-docs-'.uniqid());
    File::ensureDirectoryExists($tmp);
    config([
        'l5-swagger.defaults.paths.docs' => $tmp,
        'l5-swagger.documentations.default.paths.docs' => $tmp,
    ]);

    try {
        expect(Artisan::call('l5-swagger:generate'))->toBe(0);

        $generated = json_decode((string) file_get_contents($tmp.'/api-docs.json'), true);
        $committed = json_decode((string) file_get_contents(storage_path('api-docs/api-docs.json')), true);

        expect($generated)->toBe($committed, 'storage/api-docs/api-docs.json is stale: run php artisan l5-swagger:generate');
    } finally {
        File::deleteDirectory($tmp);
    }
});
