<x-guest-layout>
    @section('title', 'Confirme seu e-mail')
    <x-auth-heading title="Confirme seu e-mail" icon="fa-envelope-circle-check" help="Não chegou? Verifique o spam ou clique em reenviar. O link expira em 60 minutos." />
    <div class="mb-4 text-sm text-surface-600 dark:text-surface-400">
        Obrigado por se cadastrar! Antes de começar, verifique seu endereço de e-mail clicando no link que acabamos de enviar. Se não recebeu o e-mail, enviaremos outro com prazer.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-success-600 dark:text-success-400">
            Um novo link de verificação foi enviado para o e-mail informado durante o cadastro.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form autocomplete="off" method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Reenviar E-mail de Verificação
                </x-primary-button>
            </div>
        </form>

        <form autocomplete="off" method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                Sair
            </button>
        </form>
    </div>
</x-guest-layout>
