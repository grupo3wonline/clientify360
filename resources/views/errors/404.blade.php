@extends('layouts.error')

@section('title', 'Página não encontrada')
@section('code', '404')
@section('icon', 'fa-map-location-dot')
@section('heading', 'Página não encontrada')
@section('message')
    <p>A página que você procura não existe, mudou de endereço ou foi removida. Confira o endereço digitado ou volte para a página inicial.</p>
@endsection
@section('secondary')
    <a href="{{ url('/#contato') }}" class="c-btn-ghost px-6 py-3"><i class="fa-solid fa-headset text-xs" aria-hidden="true"></i> Falar com a gente</a>
@endsection
