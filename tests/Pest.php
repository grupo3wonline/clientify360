<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Vincula o TestCase base e o trait RefreshDatabase aos testes de Feature,
| permitindo escrever novos testes com a sintaxe do Pest sem quebrar os
| testes PHPUnit (baseados em classes) que já existem.
|
*/

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/** A real temp file: unlike UploadedFile::fake(), its MIME type is sniffed from the content (finfo). */
function realUpload(string $name, string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'upl');
    file_put_contents($path, $content);

    return new UploadedFile($path, $name, null, null, true);
}
