<section id="logotipo" class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-surface-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-image text-brand-500" aria-hidden="true"></i>
            Logotipo da conta
            <x-tooltip text="O logotipo aparece no topo dos PDFs de orçamentos e ordens de serviço enviados aos seus clientes." />
        </h2>
        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
            Envie uma imagem PNG, JPG ou WEBP de até 2 MB (máximo 2000 × 2000 pixels). Dica: fundo transparente fica melhor no PDF.
        </p>
    </header>

    @php($logo = $account?->getFirstMedia('logo'))

    @if($logo)
        <div class="flex items-center gap-4">
            <img src="{{ route('account.logo.show') }}?v={{ $logo->updated_at?->timestamp }}" alt="Logotipo atual da conta {{ $account->name }}"
                 class="h-16 max-w-[12rem] object-contain rounded-lg border border-surface-200 dark:border-surface-700 bg-white p-2" loading="lazy">
            <x-confirm-delete :action="route('account.logo.destroy')" title="Remover o logotipo?"
                              text="Os próximos PDFs mostrarão apenas o nome da conta." confirm="Remover" label="Remover logotipo" />
        </div>
    @endif

    <form method="POST" action="{{ route('account.logo.update') }}" enctype="multipart/form-data" autocomplete="off" class="space-y-3">
        @csrf
        <div>
            <x-input-label for="logo" value="{{ $logo ? 'Trocar logotipo' : 'Enviar logotipo' }}" />
            <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" required autocomplete="off"
                   class="mt-1 block w-full text-sm text-surface-700 dark:text-surface-300 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700">
            <x-input-error class="mt-2" :messages="$errors->accountLogo->get('logo')" />
        </div>
        <x-primary-button>
            <i class="fa-solid fa-upload mr-2" aria-hidden="true"></i> Salvar logotipo
        </x-primary-button>
    </form>
</section>
