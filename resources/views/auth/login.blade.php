<x-guest-layout>
    @section('title', 'Entrar')
    <x-auth-heading title="Bem-vindo de volta" subtitle="Entre na sua conta para continuar" icon="fa-right-to-bracket"
                    help="Por segurança, informe os três dados cadastrados: nome de usuário, e-mail e senha." />

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form autocomplete="off" method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Honeypot anti-bot: invisível para pessoas, apenas robôs preenchem. --}}
        <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
            <label for="website">Não preencha este campo</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <!-- Username -->
        <div>
            <label for="username" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Nome de usuário</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="off"
                   placeholder="seu_usuario"
                   class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
            <x-input-error :messages="$errors->get('username')" class="mt-1.5" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="off"
                   placeholder="seu@email.com"
                   class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Senha</label>
            <input id="password" type="password" name="password" required autocomplete="off"
                   placeholder="••••••••"
                   class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input autocomplete="off" id="remember_me" type="checkbox" class="rounded border-surface-300 dark:border-surface-600 text-brand-600 shadow-sm focus:ring-brand-500 dark:bg-surface-800" name="remember">
                <span class="ms-2 text-sm text-surface-600 dark:text-surface-400">Lembrar-me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium transition" href="{{ route('password.request') }}">
                    Esqueceu a senha?
                </a>
            @endif
        </div>

        <x-recaptcha action="login" />

        <!-- Submit -->
        <div class="mt-6">
            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition shadow-sm shadow-brand-600/25 text-sm">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Entrar
            </button>
        </div>

        <!-- Register Link -->
        <div class="mt-6 text-center">
            <span class="text-sm text-surface-500 dark:text-surface-400">Não tem conta?</span>
            <a href="{{ route('register') }}" class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium ml-1 transition">Cadastre-se</a>
        </div>
    </form>
</x-guest-layout>
