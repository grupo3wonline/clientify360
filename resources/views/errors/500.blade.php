@extends('layouts.error')

@section('title', 'Erro interno')
@section('code', '500')
@section('icon', 'fa-triangle-exclamation')
@section('tone', 'bg-danger-50 text-danger-600 dark:bg-danger-950/40 dark:text-danger-400')
@section('heading', 'Erro interno')
@section('message')
    <p>Algo deu errado do nosso lado e a sua solicitação não foi concluída. O erro foi registrado para a nossa equipe analisar.</p>
    <p class="text-sm text-surface-500 dark:text-surface-400">Tente novamente em alguns minutos. Seus dados continuam seguros.</p>
@endsection
