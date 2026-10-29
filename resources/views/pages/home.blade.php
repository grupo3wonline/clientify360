@extends('layouts.public')

{{-- Unified sales page: Home + Sobre + Serviços + Ferramentas + Contato (the old URLs 301-redirect to the anchors). --}}
@php($pro = $plans['professional'])
@section('seo')
    <x-seo
        :title="'Clientify360 — CRM com IA para pequenos negócios | '.$pro->formattedPrice().'/mês'"
        :description="'Organize clientes, funil de vendas, orçamentos, agenda e financeiro em um só lugar. Comece grátis e evolua para o Profissional por '.$pro->formattedPrice().'/mês.'"
        :canonical="url('/')"
        :breadcrumbs="[['Início', url('/')]]"
        :schema="$schema"
    />
@endsection
@section('cta_placement', 'landing-final')

@section('content')
    @include('pages.home._hero')
    @include('pages.home._benefits')
    @include('pages.home._features')
    @include('pages.home._how-it-works')
    @include('pages.home._tips')
    @include('pages.home._services')
    @include('pages.home._tools')
    @include('pages.home._plans')
    @include('pages.home._testimonials')
    @include('pages.home._faq')
    @include('pages.home._about')
    @include('pages.home._contact')
    @include('pages.home._final-cta')

    {{-- Floating button: back to top --}}
    <button type="button"
        x-data="{ show: false }"
        x-init="window.addEventListener('scroll', () => show = window.scrollY > 600, { passive: true })"
        x-show="show" x-transition x-cloak
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-40 w-12 h-12 bg-brand-600 hover:bg-brand-700 text-white rounded-full shadow-lg shadow-brand-600/30 flex items-center justify-center transition"
        aria-label="Voltar ao topo" title="Voltar ao topo">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>
@endsection
