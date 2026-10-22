@extends('layouts.public')

@section('title', 'Sessão Expirada')
@section('meta_description', 'Sua sessão expirou. Por favor, recarregue a página e tente novamente.')

@section('content')
    <section class="min-h-[60vh] flex items-center justify-center py-16 md:py-24">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <i class="fa-solid fa-clock-rotate-left text-warning-500 dark:text-warning-400 text-6xl md:text-8xl" aria-hidden="true"></i>
            </div>
            <p class="text-7xl md:text-9xl font-bold text-warning-500 dark:text-warning-400 mb-4" aria-hidden="true">419</p>
            <h1 class="text-2xl md:text-3xl font-bold text-surface-800 dark:text-white mb-4">Sessão expirada</h1>
            <p class="text-surface-600 dark:text-surface-300 mb-8 leading-relaxed">
                Sua sessão expirou por inatividade. Por motivos de segurança, recarregue a página e tente novamente. Se o problema persistir, faça login novamente.
            </p>
            <a href="{{ url('/') }}" class="inline-flex items-center bg-brand-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-700 transition shadow-lg">
                <i class="fa-solid fa-house mr-2" aria-hidden="true"></i> Voltar para a Página Inicial
            </a>
        </div>
    </section>
@endsection
