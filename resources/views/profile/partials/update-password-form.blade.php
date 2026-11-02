<section>
    <header>
        <h2 class="text-lg font-medium text-surface-900 dark:text-white">
            Atualizar Senha
        </h2>

        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
            Use uma senha longa e aleatória para manter sua conta segura.
        </p>
        @if($expiresAt = auth()->user()->passwordExpiresAt())
            <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">
                <i class="fa-solid fa-clock mr-1" aria-hidden="true"></i>
                Por segurança, a senha é trocada a cada {{ config('security.password.max_age_days') }} dias.
                A atual vale até <strong>{{ $expiresAt->format('d/m/Y') }}</strong>
                @if(auth()->user()->passwordExpiresSoon())
                    <span class="font-semibold text-warning-700 dark:text-warning-300">(troque agora)</span>
                @endif.
            </p>
        @endif
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6" autocomplete="off">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Senha Atual" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="off" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nova Senha" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="off" />
            <p class="mt-1 text-xs text-surface-500 dark:text-surface-400">Use ao menos 8 caracteres, combinando letras, números e símbolos.</p>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar Nova Senha" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="off" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Salvar</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-accent-600 dark:text-accent-400"
                >Senha atualizada com sucesso.</p>
            @endif
        </div>
    </form>
</section>
