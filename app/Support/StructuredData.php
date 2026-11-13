<?php

declare(strict_types=1);

namespace App\Support;

use App\Data\PlanData;

/**
 * Builds schema.org JSON-LD nodes (SEO/GEO/AEO) for the public pages.
 * Pure: every method returns a plain array; encode() renders the @graph.
 */
final class StructuredData
{
    public const ORGANIZATION_ID = 'https://www.3wonline.com.br/#organization';

    /** @return array<string, mixed> */
    public static function organization(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => self::ORGANIZATION_ID,
            'name' => 'Grupo 3W Online',
            'url' => 'https://www.3wonline.com.br',
            'email' => 'contato@3wonline.com.br',
            'telephone' => '+55 41 99692-4829',
            'description' => 'Empresa brasileira que desenvolve sites e sistemas personalizados sob demanda e mantém o Clientify360.',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => 'contato@3wonline.com.br',
                'telephone' => '+55 41 99692-4829',
                'availableLanguage' => 'pt-BR',
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'name' => 'Clientify360',
            'url' => url('/'),
            'inLanguage' => 'pt-BR',
            'publisher' => ['@id' => self::ORGANIZATION_ID],
        ];
    }

    /** @return array<string, mixed> */
    public static function softwareApplication(PlanData ...$plans): array
    {
        return [
            '@type' => 'SoftwareApplication',
            '@id' => url('/').'#software',
            'name' => 'Clientify360',
            'url' => url('/'),
            'description' => 'CRM online para pequenos negócios: clientes, funil de vendas, orçamentos, ordens de serviço, agenda, estoque e financeiro em um só lugar, com plano gratuito.',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'inLanguage' => 'pt-BR',
            'offers' => array_map(fn (PlanData $plan): array => [
                '@type' => 'Offer',
                'name' => 'Plano '.$plan->name,
                'price' => self::price($plan->price),
                'priceCurrency' => 'BRL',
                'url' => url('/#planos'),
            ], array_values($plans)),
            'publisher' => ['@id' => self::ORGANIZATION_ID],
        ];
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @return array<string, mixed>
     */
    public static function faqPage(array $faqs): array
    {
        return [
            '@type' => 'FAQPage',
            '@id' => url('/').'#faq',
            'mainEntity' => array_map(fn (array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ], array_values($faqs)),
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $items  [name, url] pairs
     * @return array<string, mixed>
     */
    public static function breadcrumbList(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item[0],
                'item' => $item[1],
            ], array_values($items), array_keys(array_values($items))),
        ];
    }

    /** @return array<string, mixed> */
    public static function webPage(string $name, string $url): array
    {
        return [
            '@type' => 'WebPage',
            '@id' => $url.'#webpage',
            'name' => $name,
            'url' => $url,
            'inLanguage' => 'pt-BR',
            'isPartOf' => ['@id' => url('/').'#website'],
            'publisher' => ['@id' => self::ORGANIZATION_ID],
        ];
    }

    /**
     * One JSON-LD document with every node in @graph. JSON_HEX_TAG keeps
     * "</script>" in content from closing the script element.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     */
    public static function encode(array $nodes): string
    {
        return (string) json_encode(
            ['@context' => 'https://schema.org', '@graph' => array_values($nodes)],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG,
        );
    }

    /**
     * LocalBusiness / SoftwareApplication hybrid for GEO SEO.
     * Signals to Google that the company is based in Curitiba, PR, Brazil.
     *
     * @return array<string, mixed>
     */
    public static function localBusiness(): array
    {
        return [
            '@type' => ['LocalBusiness', 'SoftwareApplication'],
            '@id' => url('/').'#localbusiness',
            'name' => 'Clientify360',
            'alternateName' => 'Clientify 360',
            'description' => 'CRM online para pequenos negócios: clientes, funil de vendas, orçamentos, ordens de serviço, agenda, estoque e financeiro em um só lugar, com plano gratuito.',
            'url' => url('/'),
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'inLanguage' => 'pt-BR',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Curitiba',
                'addressRegion' => 'PR',
                'addressCountry' => 'BR',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => '-25.4284',
                'longitude' => '-49.2733',
            ],
            'areaServed' => [
                ['@type' => 'Country', 'name' => 'Brasil'],
                ['@type' => 'Country', 'name' => 'América Latina'],
            ],
            'currenciesAccepted' => 'BRL',
            'paymentAccepted' => 'Cartão de crédito, Boleto (via Stripe)',
            'openingHours' => 'Mo-Su 00:00-24:00',
            'priceRange' => 'R$ 0 – R$ 29,90/mês',
            'parentOrganization' => ['@id' => self::ORGANIZATION_ID],
            'publisher' => ['@id' => self::ORGANIZATION_ID],
        ];
    }

    /**
     * HowTo schema for the "como funciona" (how it works) section.
     * Improves visibility in Google's rich results and AI answer boxes.
     *
     * @param  array<int, array{name: string, text: string}>  $steps
     * @return array<string, mixed>
     */
    public static function howTo(array $steps): array
    {
        return [
            '@type' => 'HowTo',
            '@id' => url('/').'#howto',
            'name' => 'Como usar o Clientify360 para organizar seu negócio',
            'description' => 'Passo a passo para começar a usar o CRM Clientify360 e ter seus clientes, vendas e agenda organizados em minutos.',
            'totalTime' => 'PT5M',
            'inLanguage' => 'pt-BR',
            'step' => array_map(fn (array $step, int $i): array => [
                '@type' => 'HowToStep',
                'position' => $i + 1,
                'name' => $step['name'],
                'text' => $step['text'],
            ], array_values($steps), array_keys(array_values($steps))),
        ];
    }

    /**
     * Speakable schema — marks sections suitable for text-to-speech
     * and AI answer engines (AEO).
     *
     * @return array<string, mixed>
     */
    public static function speakable(): array
    {
        return [
            '@type' => 'WebPage',
            '@id' => url('/').'#speakable',
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => ['#hero-title', '#beneficios-title', '#como-funciona-title', '#planos-title'],
            ],
            'url' => url('/'),
        ];
    }

    /**
     * Product schema for the Professional plan — improves visibility in
     * "best CRM" queries and AI comparison answers.
     *
     * @return array<string, mixed>
     */
    public static function product(PlanData $plan): array
    {
        return [
            '@type' => 'Product',
            '@id' => url('/').'#product-'.$plan->slug,
            'name' => 'Clientify360 Plano '.ucfirst($plan->name),
            'description' => $plan->description,
            'brand' => ['@id' => self::ORGANIZATION_ID],
            'url' => url('/#planos'),
            'offers' => [
                '@type' => 'Offer',
                'name' => 'Plano '.ucfirst($plan->name),
                'price' => self::price($plan->price),
                'priceCurrency' => 'BRL',
                'priceValidUntil' => now()->addYear()->toDateString(),
                'availability' => 'https://schema.org/InStock',
                'url' => url('/#planos'),
                'seller' => ['@id' => self::ORGANIZATION_ID],
            ],
        ];
    }

    /** "0" for free plans, "29.90" otherwise (schema.org expects a dot decimal). */
    private static function price(float $price): string
    {
        return $price <= 0 ? '0' : number_format($price, 2, '.', '');
    }
}
