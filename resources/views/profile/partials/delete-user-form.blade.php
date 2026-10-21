<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-surface-900 dark:text-white">
            Excluir Conta
        </h2>

        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
            Ao excluir sua conta, todos os seus recursos e dados serão apagados permanentemente e não poderão ser recuperados. Antes de continuar, baixe qualquer informação que deseje guardar. Este é o seu direito de exclusão previsto na LGPD.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Excluir Conta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6" autocomplete="off">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-surface-900 dark:text-white">
                Tem certeza de que deseja excluir sua conta?
            </h2>

            <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
                Esta ação é permanente e não pode ser desfeita. Digite sua senha para confirmar que deseja excluir definitivamente a sua conta.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Senha" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Senha"
                    autocomplete="off"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Excluir Conta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
