{{--
    SEO / SEM / GEO / AEO head tags for the public pages.
    title: full page title; schema: extra JSON-LD nodes (App\Support\StructuredData);
    breadcrumbs: [name, url] pairs, added to the graph as a BreadcrumbList.
    Pages with noindex get no JSON-LD.
--}}
@props([
    'title' => 'Clientify360',
    'description' => 'Clientify360: CRM para pequenos negócios, com clientes, funil de vendas, orçamentos, agenda e financeiro em um só lugar.',
    'canonical' => null,
    'image' => null,
    'imageAlt' => 'Clientify360, CRM para pequenos negócios',
    'type' => 'website',
    'noindex' => false,
    'breadcrumbs' => [],
    'schema' => [],
])

@php
    $canonical = $canonical ?: url()->current();
    $ogImage = $image ? (str_starts_with($image, 'http') ? $image : asset($image)) : asset('images/og-image.png');
    $graph = $schema;
    if ($breadcrumbs !== []) {
        $graph[] = \App\Support\StructuredData::breadcrumbList($breadcrumbs);
    }
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1' }}">
<meta name="author" content="HOTWYL | WILLFROMBRASIL">
<link rel="canonical" href="{{ $canonical }}">
<link rel="alternate" hreflang="pt-BR" href="{{ $canonical }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

{{-- Open Graph (Facebook, LinkedIn, WhatsApp) --}}
<meta property="og:site_name" content="Clientify360">
<meta property="og:locale" content="pt_BR">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $imageAlt }}">

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ $imageAlt }}">

@if(! $noindex && $graph !== [])
    <script type="application/ld+json">{!! \App\Support\StructuredData::encode($graph) !!}</script>
@endif
