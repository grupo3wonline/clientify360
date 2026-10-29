{{--
    Shared <head> for the public, guest and app layouts.
    Variables: $noindex (bool), $title (string|null), $description (string|null), $seo (bool: render x-seo slot by the caller).
--}}
@include('layouts.partials.theme-init')
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="{{ config('brand.colors.brand.600') }}">
<meta name="format-detection" content="telephone=no">
<meta name="application-name" content="Clientify360">
<link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
<link rel="alternate icon" href="{{ asset('favicon.ico') }}">
<link rel="preload" as="font" type="font/woff2" crossorigin href="{{ Vite::asset('resources/fonts/inter-latin-wght-normal.woff2') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@if($noindex ?? false)
    <meta name="robots" content="noindex, nofollow">
    <title>{{ ($title ?? null) ? $title.' | Clientify360' : 'Clientify360' }}</title>
    <meta name="description" content="{{ $description ?? 'Clientify360: CRM para pequenos negócios.' }}">
@endif
<x-analytics />
