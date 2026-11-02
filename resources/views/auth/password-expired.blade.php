<x-guest-layout>
    @section('title', 'Sua senha expirou')
    <x-auth-heading title="Sua senha expirou" subtitle="Por segurança, as senhas são trocadas a cada {{ config('security.password.max_age_days') }} dias. Defina uma nova para continuar." icon="fa-key"
                    help="Use uma senha que você não usa em outros sites. A nova senha precisa ser diferente da atual." />

    <form autocomplete="off" method="POST" action="{{ route('password.expired.update') }}" data-loading-text="Atualizando senha...">
        @csrf

        <div>
            <x-input-label for="current_password" value="Senha atual" />
            <x-text-input id="current_password" class="block mt-1 w-full" type="password" name="current_password" required autofocus autocomplete="off" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Nova senha" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="off" />
            <p class="mt-1 text-xs text-surface-500 dark:text-surface-400">
                {{ app()->isProduction() ? 'Mínimo de 10 caracteres, com maiúsculas, minúsculas e números.' : 'Mínimo de 8 caracteres.' }}
            </p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmar nova senha" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="off" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <button type="submit" form="logout-expired" class="text-sm text-surface-500 hover:text-surface-700 dark:text-surface-400 dark:hover:text-surface-200 underline transition">
                Sair
            </button>
            <x-primary-button>
                <i class="fa-solid fa-key mr-2" aria-hidden="true"></i> Salvar nova senha
            </x-primary-button>
        </div>
    </form>

    <form id="logout-expired" method="POST" action="{{ route('logout') }}" autocomplete="off">@csrf</form>
</x-guest-layout>
