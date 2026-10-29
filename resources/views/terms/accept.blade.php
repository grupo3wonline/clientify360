<x-guest-layout>
    @section('title', 'Aceite dos Termos de Uso')
    <div x-data="{ accepted: false, submitting: false }" class="w-full">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-file-contract text-brand-600 dark:text-brand-400 text-xl" aria-hidden="true"></i>
            </div>
            <h1 class="text-xl font-bold text-surface-900 dark:text-white">Termos de Uso</h1>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Para continuar, leia e aceite a versão atual dos termos</p>
        </div>

        <!-- Terms Content -->
        <div class="relative mb-5">
            <div role="document" aria-label="Termos de Uso do Clientify360" tabindex="0"
                 class="max-h-64 overflow-y-auto border border-surface-200 dark:border-surface-700 rounded-xl p-4 text-sm scrollbar-thin focus:ring-2 focus:ring-brand-500 focus:outline-none">
                @include('terms._content', [
                    'headingTag' => 'h2',
                    'headingClass' => 'font-bold text-surface-800 dark:text-surface-200 mt-4 mb-1',
                ])
            </div>
            <!-- Scroll hint gradient -->
            <div class="absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-white dark:from-surface-900 to-transparent rounded-b-xl pointer-events-none" aria-hidden="true"></div>
        </div>
        <p class="mb-4 text-xs text-surface-500 dark:text-surface-400">
            Prefere ler em tela cheia? <a href="{{ route('terms.show') }}#aceitacao" target="_blank" rel="noopener noreferrer" class="text-brand-600 dark:text-brand-400 hover:underline">Abrir os Termos de Uso</a>.
        </p>

        <!-- Accept Form -->
        <form autocomplete="off" method="POST" action="{{ route('terms.accept') }}" @submit="submitting = true">
            @csrf

            <div class="mb-5">
                <label for="accept_terms" class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" id="accept_terms" name="accept_terms" value="1"
                           autocomplete="off" aria-required="true"
                           x-model="accepted"
                           class="mt-0.5 rounded border-surface-300 dark:border-surface-600 text-brand-600 shadow-sm focus:ring-brand-500 dark:bg-surface-800"
                           @if(old('accept_terms')) checked @endif>
                    <span class="text-sm text-surface-700 dark:text-surface-300">
                        Li e aceito os <strong>Termos de Uso</strong> e a <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer" class="text-brand-600 dark:text-brand-400 hover:underline">Política de Privacidade</a> do Clientify360.
                    </span>
                </label>
                @error('accept_terms')
                    <p class="mt-2 text-xs text-danger-600 dark:text-danger-400 flex items-center" role="alert">
                        <i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit"
                    :disabled="!accepted || submitting"
                    :class="{ 'opacity-50 cursor-not-allowed': !accepted || submitting }"
                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition shadow-sm shadow-brand-600/25 text-sm disabled:hover:bg-brand-600">
                <template x-if="submitting">
                    <i class="fa-solid fa-spinner fa-spin mr-2" aria-hidden="true"></i>
                </template>
                <template x-if="!submitting">
                    <i class="fa-solid fa-check-circle mr-2" aria-hidden="true"></i>
                </template>
                <span x-text="submitting ? 'Processando...' : 'Aceitar e Continuar'"></span>
            </button>
        </form>

        <!-- Logout option -->
        <div class="mt-4 text-center">
            <form autocomplete="off" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-surface-500 dark:text-surface-400 hover:text-danger-600 dark:hover:text-danger-400 transition">
                    <i class="fa-solid fa-right-from-bracket mr-1" aria-hidden="true"></i> Sair da conta
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
