<x-guest-layout>
    @isset($invitation)
        @if($invitation)
            <div class="mb-4 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 p-4 text-sm text-indigo-800 dark:text-indigo-200">
                <i class="fa-solid fa-user-plus mr-1"></i>
                Você foi convidado para a conta <strong>{{ $invitation->account->name }}</strong>
                como <strong>{{ \App\Models\User::ROLE_LABELS[$invitation->role] ?? $invitation->role }}</strong>.
                Complete o cadastro para entrar na equipe.
            </div>
        @endif
    @endisset

    <form method="POST" action="{{ route('register') }}">
        @csrf
        @if(!empty($inviteToken))
            <input type="hidden" name="invite_token" value="{{ $inviteToken }}">
        @endif

        {{-- Honeypot anti-bot: invisível para pessoas, apenas robôs preenchem. --}}
        <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
            <label for="website">Não preencha este campo</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nome Completo" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="off" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
            <x-input-label for="username" value="Nome de Usuário" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autocomplete="off" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="off" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="off" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmar Senha" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="off" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- LGPD Consent -->
        <div class="mt-4">
            <label class="flex items-start">
                <input type="checkbox" name="lgpd_consent" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mt-1" required>
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                    Li e concordo com a <a href="{{ route('privacy') }}" target="_blank" class="text-indigo-600 hover:underline">Política de Privacidade</a> e autorizo o tratamento dos meus dados pessoais conforme a LGPD.
                </span>
            </label>
            <x-input-error :messages="$errors->get('lgpd_consent')" class="mt-2" />
        </div>

        <x-recaptcha action="register" />

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                Já possui uma conta?
            </a>

            <x-primary-button class="ms-4">
                Cadastrar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
