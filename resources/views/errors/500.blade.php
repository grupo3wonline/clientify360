@extends('layouts.public')

@section('title', 'Erro Interno do Servidor')
@section('meta_description', 'Ocorreu um erro interno no servidor. Nossa equipe já foi notificada e está trabalhando na solução.')

@section('content')
    <section class="min-h-[60vh] flex items-center justify-center py-16 md:py-24">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <i class="fa-solid fa-triangle-exclamation text-orange-500 dark:text-orange-400 text-6xl md:text-8xl"></i>
            </div>
            <h1 class="text-7xl md:text-9xl font-bold text-orange-500 dark:text-orange-400 mb-4">500</h1>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-4">Erro interno do servidor</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                Algo deu errado do nosso lado. Nossa equipe técnica já foi notificada e está trabalhando para resolver o problema. Tente novamente em alguns instantes.
            </p>
            <a href="{{ url('/') }}" class="inline-flex items-center bg-primary-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-primary-700 transition shadow-lg">
                <i class="fa-solid fa-house mr-2"></i> Voltar para a Página Inicial
            </a>
        </div>
    </section>
@endsection
