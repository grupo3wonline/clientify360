@extends('layouts.error')

@section('title', 'Acesso não autenticado')
@section('code', '401')
@section('icon', 'fa-user-lock')
@section('heading', 'Acesso não autenticado')
@section('message')
    <p>Para ver esta página, você precisa entrar na sua conta. Se a sua sessão terminou, basta fazer login novamente.</p>
    <p class="text-sm text-surface-500 dark:text-surface-400">Dica: o login pede nome de usuário, e-mail e senha.</p>
@endsection
@section('secondary')
    <a href="{{ url('/login') }}" class="c-btn-ghost px-6 py-3"><i class="fa-solid fa-right-to-bracket text-xs" aria-hidden="true"></i> Entrar</a>
@endsection
