@extends('layouts.public')

@section('title', 'Página não encontrada')
@section('meta_description', 'A página que você está procurando não foi encontrada. Verifique o endereço ou volte para a página inicial.')

@section('content')
    <section class="min-h-[60vh] flex items-center justify-center py-16 md:py-24">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <i class="fa-solid fa-map-location-dot text-brand-600 dark:text-brand-400 text-6xl md:text-8xl" aria-hidden="true"></i>
            </div>
            <p class="text-7xl md:text-9xl font-bold text-brand-600 dark:text-brand-400 mb-4" aria-hidden="true">404</p>
            <h1 class="text-2xl md:text-3xl font-bold text-surface-800 dark:text-white mb-4">Página não encontrada</h1>
            <p class="text-surface-600 dark:text-surface-300 mb-8 leading-relaxed">
                A página que você está procurando não existe, foi removida ou está temporariamente indisponível. Verifique o endereço digitado ou volte para a página inicial.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="inline-flex items-center bg-brand-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-700 transition shadow-lg">
                    <i class="fa-solid fa-house mr-2" aria-hidden="true"></i> Voltar para a página inicial
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-6 py-3 rounded-xl hover:bg-surface-200 dark:hover:bg-surface-700 transition">
                    <i class="fa-solid fa-headset mr-2" aria-hidden="true"></i> Falar com o suporte
                </a>
            </div>
        </div>
    </section>
@endsection
