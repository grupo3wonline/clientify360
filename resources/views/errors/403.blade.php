@extends('layouts.public')

@section('title', 'Acesso Negado')
@section('meta_description', 'Você não tem permissão para acessar esta página.')

@section('content')
    <section class="min-h-[60vh] flex items-center justify-center py-16 md:py-24">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <i class="fa-solid fa-hand text-red-500 dark:text-red-400 text-6xl md:text-8xl"></i>
            </div>
            <h1 class="text-7xl md:text-9xl font-bold text-red-500 dark:text-red-400 mb-4">403</h1>
            <h2 class="text-2xl md:text-3xl font-bold text-surface-800 dark:text-white mb-4">Acesso negado</h2>
            <p class="text-surface-600 dark:text-surface-300 mb-8 leading-relaxed">
                Você não tem permissão para acessar esta página ou recurso. Se acredita que isso é um erro, entre em contato com o suporte.
            </p>
            <a href="{{ url('/') }}" class="inline-flex items-center bg-brand-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-700 transition shadow-lg">
                <i class="fa-solid fa-house mr-2"></i> Voltar para a Página Inicial
            </a>
        </div>
    </section>
@endsection
