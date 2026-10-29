@extends('layouts.error')

@php
    $headers = isset($exception) && method_exists($exception, 'getHeaders') ? $exception->getHeaders() : [];
    $retryAfter = (int) ($headers['Retry-After'] ?? 60);
@endphp

@section('title', 'Muitas requisições')
@section('code', '429')
@section('icon', 'fa-gauge-high')
@section('tone', 'bg-warning-50 text-warning-700 dark:bg-warning-950/40 dark:text-warning-400')
@section('heading', 'Muitas requisições')
@section('message')
    <p>Recebemos muitas tentativas em pouco tempo. Esse limite protege a sua conta e o sistema contra abusos.</p>
    <p class="font-medium text-surface-800 dark:text-surface-100">Aguarde {{ $retryAfter }} segundos e tente novamente.</p>
@endsection
