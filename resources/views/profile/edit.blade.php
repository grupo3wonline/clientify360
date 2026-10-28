<x-app-layout>
    @section('title', 'Meu Perfil')

    <x-slot name="header">
        <x-breadcrumb :items="[
            ['label' => 'Meu Perfil'],
        ]" />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Meu Perfil</h1>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Gerencie suas informações pessoais, senha e privacidade da conta.</p>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-surface-900 border border-surface-200 dark:border-surface-800 shadow-sm sm:rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-surface-900 border border-surface-200 dark:border-surface-800 shadow-sm sm:rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            @can('account.settings')
                <div class="p-4 sm:p-8 bg-white dark:bg-surface-900 border border-surface-200 dark:border-surface-800 shadow-sm sm:rounded-2xl">
                    <div class="max-w-xl">
                        @include('profile.partials.account-logo')
                    </div>
                </div>
            @endcan

            <div class="p-4 sm:p-8 bg-white dark:bg-surface-900 border border-surface-200 dark:border-surface-800 shadow-sm sm:rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.api-tokens')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-surface-900 border border-surface-200 dark:border-surface-800 shadow-sm sm:rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.export-personal-data')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-surface-900 border border-surface-200 dark:border-surface-800 shadow-sm sm:rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
