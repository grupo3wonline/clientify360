{{--
    SEO / SEM / GEO / AEO head tags for the public pages.

    SEO  — title, description, canonical, robots, author, hreflang.
    SEM  — Open Graph (Facebook, LinkedIn, WhatsApp), Twitter/X cards.
    GEO  — geographic meta tags (geo.region, geo.placename, ICBM) for local/regional indexing.
    AEO  — Answer Engine Optimization: speakable schema, FAQ schema, ai:abstract, structured
           data tuned for AI-powered answer engines (ChatGPT, Perplexity, Google SGE / AI Mode).

    Required props:
      title       — full page title string
      description — 110–160 character page description

    Optional props:
      canonical   — canonical URL (defaults to current URL)
      image       — absolute URL or asset path for the OG image
      imageAlt    — alt text for the OG image
      type        — og:type (default: website)
      noindex     — bool: add noindex, nofollow
      breadcrumbs — [[name, url], ...] pairs → BreadcrumbList JSON-LD
      schema      — extra JSON-LD nodes (App\Support\StructuredData)
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
    $ogImage   = $image
        ? (str_starts_with($image, 'http') ? $image : asset($image))
        : asset('images/og-image.png');
    $graph     = $schema;
    if ($breadcrumbs !== []) {
        $graph[] = \App\Support\StructuredData::breadcrumbList($breadcrumbs);
    }
@endphp

{{-- ── Core SEO ──────────────────────────────────────────────────────── --}}
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
<meta name="author" content="HOTWYL | WILLFROMBRASIL">
<meta name="generator" content="Clientify360 / Laravel 12">
<link rel="canonical" href="{{ $canonical }}">
<link rel="alternate" hreflang="pt-BR" href="{{ $canonical }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

{{-- ── Open Graph (Facebook, LinkedIn, WhatsApp, iMessage) ───────────── --}}
<meta property="og:site_name" content="Clientify360">
<meta property="og:locale" content="pt_BR">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="{{ $imageAlt }}">
<meta property="og:updated_time" content="{{ now()->toIso8601String() }}">

{{-- ── Twitter / X ─────────────────────────────────────────────────── --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@clientify360">
<meta name="twitter:creator" content="@willfrombrasil">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ $imageAlt }}">

{{-- ── GEO — geographic metadata for local / regional indexing ───────── --}}
{{--
    Tells search engines and mapping services that this service is based in
    Curitiba, Paraná, Brazil. Helps "small business CRM Curitiba" etc.
--}}
<meta name="geo.region" content="BR-PR">
<meta name="geo.placename" content="Curitiba, Paraná, Brasil">
<meta name="geo.position" content="-25.4284;-49.2733">
<meta name="ICBM" content="-25.4284, -49.2733">
<meta name="language" content="pt-BR">
<meta name="content-language" content="pt-BR">
<meta name="revisit-after" content="7 days">
<meta name="rating" content="general">
<meta name="distribution" content="global">
<meta name="target" content="all">
<meta name="audience" content="all">
<meta name="coverage" content="Brasil, América Latina">
<meta name="classification" content="Business/Software">
<meta name="category" content="CRM, Software de gestão, Micro SaaS">

{{-- ── AEO — Answer Engine Optimization ───────────────────────────────── --}}
{{--
    These tags help AI-powered answer engines (Perplexity, ChatGPT Browse,
    Google SGE, Bing CoPilot) understand the page content and cite it correctly
    in generated answers.
--}}
<meta name="ai:abstract" content="{{ $description }}">
<meta name="ai:keywords" content="CRM, gestão de clientes, micro SaaS, funil de vendas, orçamentos, ordens de serviço, agenda, estoque, financeiro, LGPD, pequenos negócios, Brasil">
<meta name="ai:type" content="SoftwareApplication">
<meta name="ai:audience" content="pequenas empresas, autônomos, profissionais liberais, prestadores de serviço">

{{-- ── PWA / mobile app appearance ────────────────────────────────────── --}}
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Clientify360">

{{-- ── JSON-LD Structured Data ─────────────────────────────────────────── --}}
@if(! $noindex && $graph !== [])
    <script type="application/ld+json">{!! \App\Support\StructuredData::encode($graph) !!}</script>
@endif
