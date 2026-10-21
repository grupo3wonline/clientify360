@props([
    'title' => null,
    'description' => 'Clientify360 - Sistema de gestão de clientes (CRM) inteligente para pequenos negócios e profissionais autônomos.',
    'keywords' => 'gestão de clientes, CRM, micro saas, sistema de clientes, agenda, lembretes, atendimento',
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'schema' => null,
])

@php
    $appName = config('app.name', 'Clientify360');
    $fullTitle = $title ? $title.' | '.$appName : $appName.' - Gestão Inteligente de Clientes';
    $canonical = url()->current();
    $ogImage = $image ? (str_starts_with($image, 'http') ? $image : asset($image)) : asset('images/og-image.png');
    $locale = str_replace('-', '_', app()->getLocale());
@endphp

{{-- SEO básico --}}
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="author" content="HOTWYL | WILLFROMBRASIL">
<meta name="publisher" content="Grupo 3W Online">
<meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1' }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph (Facebook, LinkedIn, WhatsApp) --}}
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:locale" content="{{ $locale }}">

{{-- Twitter Cards --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">

{{-- Structured Data (GEO/AEO): ajuda motores de busca e mecanismos de resposta/IA a entender o conteúdo --}}
<script type="application/ld+json">
{!! json_encode($schema ?? [
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => $appName,
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem' => 'Web',
    'description' => $description,
    'url' => url('/'),
    'inLanguage' => 'pt-BR',
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'BRL',
        'price' => '0',
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Grupo 3W Online',
        'url' => 'https://www.3wonline.com.br',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>
