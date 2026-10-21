<x-app-layout>
    @section('title', 'Novo agendamento')

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <a href="{{ route('appointments.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Agendamentos</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Novo</span>
        </nav>
    </x-slot>

    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Novo agendamento</h1>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Registre uma reunião, visita, ligação ou videochamada.</p>
            </div>
            <a href="{{ route('appointments.index') }}" class="inline-flex items-center px-3 py-2 text-sm rounded-xl text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                <i class="fa-solid fa-arrow-left mr-2 text-xs"></i> Voltar
            </a>
        </div>

        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf
                @include('appointments._form')

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('appointments.index') }}" class="px-4 py-2.5 text-sm rounded-xl text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition">Cancelar</a>
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                        <i class="fa-solid fa-check mr-2 text-xs"></i> Salvar agendamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
