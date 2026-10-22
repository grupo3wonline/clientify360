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
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');
