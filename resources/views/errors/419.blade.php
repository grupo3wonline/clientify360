@extends('layouts.error')

@section('title', 'Sessão expirada')
@section('code', '419')
@section('icon', 'fa-clock-rotate-left')
@section('tone', 'bg-warning-50 text-warning-700 dark:bg-warning-950/40 dark:text-warning-400')
@section('heading', 'Sessão expirada')
@section('message')
    <p>Por segurança, o formulário expirou depois de um tempo sem uso. Recarregue a página e envie novamente.</p>
    <p class="text-sm text-surface-500 dark:text-surface-400">Se o problema continuar, saia e entre de novo na sua conta.</p>
@endsection
@section('secondary')
    @php
        // Reload the page the form came from (a fresh GET gets a new CSRF token). Read from the request, never the session.
        $referer = (string) request()->headers->get('referer', '');
        $reloadUrl = $referer !== '' && parse_url($referer, PHP_URL_HOST) === request()->getHost() ? $referer : url('/');
    @endphp
    <a href="{{ $reloadUrl }}" class="c-btn-ghost px-6 py-3">
        <i class="fa-solid fa-rotate-right text-xs" aria-hidden="true"></i> Recarregar a página
    </a>
@endsection
