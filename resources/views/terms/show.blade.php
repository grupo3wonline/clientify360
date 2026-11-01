@extends('layouts.public')

@section('seo')
    <x-seo
        title="Termos de Uso | Clientify360"
        description="Termos de Uso do Clientify360: planos e cobrança, cancelamento, uso aceitável, propriedade dos dados, API, responsabilidade, foro de Curitiba/PR e contato."
        :canonical="route('terms.show')"
        :breadcrumbs="[['Início', url('/')], ['Termos de Uso', route('terms.show')]]"
        :schema="[\App\Support\StructuredData::webPage('Termos de Uso', route('terms.show'))]"
    />
@endsection
@section('cta_placement', 'terms')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-800 dark:from-brand-800 dark:to-surface-900 text-white py-14 md:py-20" aria-labelledby="terms-title">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 id="terms-title" class="text-3xl md:text-5xl font-bold mb-4">Termos de Uso</h1>
            <p class="text-lg text-brand-100 max-w-3xl mx-auto">As regras para usar o Clientify360, em linguagem direta. Leia junto com a Política de Privacidade.</p>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <x-breadcrumb :public="true" :items="[['label' => 'Termos de Uso']]" />
        <article class="c-card p-6 md:p-10">
            @include('terms._content')
        </article>
    </div>
@endsection
