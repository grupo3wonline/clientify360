<x-guest-layout>
    @section('title', 'Recuperar senha')
    <x-auth-heading title="Recuperar senha" icon="fa-key" help="O link de redefinição vale por 60 minutos. Confira também a caixa de spam." />
    <div class="mb-4 text-sm text-surface-600 dark:text-surface-400">
        Esqueceu sua senha? Sem problemas. Informe seu e-mail e enviaremos um link para redefinição de senha.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form autocomplete="off" method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Honeypot anti-bot: invisível para pessoas, apenas robôs preenchem. --}}
        <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
            <label for="website">Não preencha este campo</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="off" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-recaptcha action="forgot" />

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Enviar Link de Redefinição
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
