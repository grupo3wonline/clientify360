<?php

declare(strict_types=1);

use App\Data\PlanData;
use App\Support\StructuredData;

it('builds the Organization node for Grupo 3W Online', function (): void {
    $node = StructuredData::organization();

    expect($node['@type'])->toBe('Organization')
        ->and($node['@id'])->toBe('https://www.3wonline.com.br/#organization')
        ->and($node['name'])->toBe('Grupo 3W Online')
        ->and($node['email'])->toBe('contato@3wonline.com.br')
        ->and($node['contactPoint']['availableLanguage'])->toBe('pt-BR');
});

it('builds one Offer per plan with schema.org prices in BRL', function (): void {
    $node = StructuredData::softwareApplication(...array_values(PlanData::catalog()));

    expect($node['@type'])->toBe('SoftwareApplication')
        ->and($node['applicationCategory'])->toBe('BusinessApplication')
        ->and($node['offers'])->toHaveCount(2)
        ->and(array_column($node['offers'], 'price'))->toBe(['0', '29.90'])
        ->and(array_unique(array_column($node['offers'], 'priceCurrency')))->toBe(['BRL']);
});

it('builds FAQPage, BreadcrumbList, WebSite and WebPage nodes', function (): void {
    $faq = StructuredData::faqPage([['question' => 'Pergunta?', 'answer' => 'Resposta.']]);
    $crumbs = StructuredData::breadcrumbList([['Início', 'https://exemplo.test/'], ['Termos', 'https://exemplo.test/termos']]);

    expect($faq['mainEntity'][0])->toBe([
        '@type' => 'Question',
        'name' => 'Pergunta?',
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Resposta.'],
    ])
        ->and(array_column($crumbs['itemListElement'], 'position'))->toBe([1, 2])
        ->and($crumbs['itemListElement'][1]['name'])->toBe('Termos')
        ->and(StructuredData::website()['inLanguage'])->toBe('pt-BR')
        ->and(StructuredData::webPage('Termos', 'https://exemplo.test/termos')['url'])->toBe('https://exemplo.test/termos');
});

it('encodes a single @graph and escapes </script> from content', function (): void {
    $json = StructuredData::encode([StructuredData::faqPage([['question' => '</script><b>x', 'answer' => 'ç']])]);

    expect($json)->not->toContain('</script>')
        ->toContain('\u003C/script\u003E')
        ->toContain('ç')
        ->and(json_decode($json, true)['@context'])->toBe('https://schema.org')
        ->and(json_decode($json, true)['@graph'][0]['@type'])->toBe('FAQPage');
});
