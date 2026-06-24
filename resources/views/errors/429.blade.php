@extends('layouts.public')

@section('title', 'Muitas Requisições')
@section('meta_description', 'Você realizou muitas requisições em um curto período. Aguarde alguns instantes e tente novamente.')

@section('content')
    <section class="min-h-[60vh] flex items-center justify-center py-16 md:py-24">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <i class="fa-solid fa-gauge-simple-high text-purple-500 dark:text-purple-400 text-6xl md:text-8xl"></i>
            </div>
            <h1 class="text-7xl md:text-9xl font-bold text-purple-500 dark:text-purple-400 mb-4">429</h1>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-4">Muitas requisições</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                Você realizou muitas requisições em um curto período de tempo. Por favor, aguarde alguns instantes antes de tentar novamente. Isso ajuda a manter o sistema seguro e estável para todos.
            </p>
            <a href="{{ url('/') }}" class="inline-flex items-center bg-primary-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-primary-700 transition shadow-lg">
                <i class="fa-solid fa-house mr-2"></i> Voltar para a Página Inicial
            </a>
        </div>
    </section>
@endsection
