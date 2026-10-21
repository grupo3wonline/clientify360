<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-surface-900 dark:text-white">Bem-vindo de volta</h2>
        <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Entre na sua conta para continuar</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Login (Email or Username) -->
        <div>
            <label for="login" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">E-mail ou Nome de Usuário</label>
            <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="off"
                   placeholder="seu@email.com ou seu_usuario"
                   class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
            <x-input-error :messages="$errors->get('login')" class="mt-1.5" />
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
                <input id="remember_me" type="checkbox" class="rounded border-surface-300 dark:border-surface-600 text-brand-600 shadow-sm focus:ring-brand-500 dark:bg-surface-800" name="remember" autocomplete="off">
                <span class="ms-2 text-sm text-surface-600 dark:text-surface-400">Lembrar-me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium transition" href="{{ route('password.request') }}">
                    Esqueceu a senha?
                </a>
            @endif
        </div>

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
