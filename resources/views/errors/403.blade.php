@extends('layouts.error')

@php
    $reason = isset($exception) ? trim((string) $exception->getMessage()) : '';
    $planRelated = $reason !== '' && str_contains(__($reason), 'plano Profissional');
    $user = auth()->hasUser() ? auth()->user() : null;
@endphp

@section('title', 'Acesso negado')
@section('code', '403')
@section('icon', 'fa-hand')
@section('tone', 'bg-danger-50 text-danger-600 dark:bg-danger-950/40 dark:text-danger-400')
@section('heading', 'Acesso negado')
@section('message')
    <p class="font-medium text-surface-800 dark:text-surface-100">{{ $reason !== '' ? __($reason) : 'Você não tem permissão para acessar esta página.' }}</p>
    @if($planRelated && $user)
        @if($user->can('account.billing'))
            <p>Este recurso faz parte do plano Profissional. Faça o upgrade quando quiser e libere o acesso na hora.</p>
        @else
            <p>Fale com o dono da conta para fazer o upgrade.</p>
        @endif
    @else
        <p>Se você acredita que deveria ter acesso, peça ao dono da conta para revisar as permissões do seu usuário em Equipe.</p>
    @endif
@endsection
@section('secondary')
    @if($planRelated && $user && $user->can('account.billing'))
        <a href="{{ route('billing.index') }}" class="c-btn-success px-6 py-3"><i class="fa-solid fa-crown text-xs" aria-hidden="true"></i> Conhecer o plano Profissional</a>
    @elseif($user)
        <a href="{{ url('/dashboard') }}" class="c-btn-ghost px-6 py-3"><i class="fa-solid fa-gauge-high text-xs" aria-hidden="true"></i> Ir para o painel</a>
    @endif
@endsection
