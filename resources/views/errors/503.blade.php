@extends('layouts.error')

@section('title', 'Em manutenção')
@section('code', '503')
@section('icon', 'fa-screwdriver-wrench')
@section('heading', 'Em manutenção')
@section('message')
    <p>Estamos fazendo uma atualização rápida para melhorar o Clientify360. O sistema volta em instantes.</p>
    <p class="text-sm text-surface-500 dark:text-surface-400">Nenhum dado é perdido durante a manutenção.</p>
@endsection
