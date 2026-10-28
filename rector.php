<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withPhpSets(php82: true)
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
        LaravelSetList::LARAVEL_120,
        LaravelSetList::LARAVEL_CODE_QUALITY,
    ])
    ->withSkip([
        __DIR__.'/app/Http/Controllers/Auth',
        __DIR__.'/bootstrap/cache',
    ])
    ->withImportNames(removeUnusedImports: true);
