<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-surface-900 dark:text-white">
            Baixar meus dados
        </h2>

        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
            Baixe uma cópia dos seus dados pessoais em formato JSON: identificação, consentimentos e o seu papel na conta. É o seu direito de acesso e portabilidade previsto na LGPD.
        </p>
    </header>

    <div class="rounded-lg bg-surface-50 dark:bg-surface-800/50 border border-surface-200 dark:border-surface-700 p-4">
        <p class="text-sm text-surface-700 dark:text-surface-300 flex items-start gap-2">
            <i class="fa-solid fa-circle-info mt-0.5 text-brand-500" aria-hidden="true"></i>
            <span><strong>Dica:</strong> o arquivo contém apenas os seus dados. Nenhuma informação de outros usuários, senha ou chave de acesso é incluída. Guarde-o em local seguro.</span>
        </p>
    </div>

    <x-primary-link
        :href="route('profile.export')"
        class="w-full sm:w-auto gap-2"
        aria-label="Baixar uma cópia dos seus dados pessoais em formato JSON"
        title="Baixar uma cópia dos seus dados pessoais em formato JSON">
        <i class="fa-solid fa-download" aria-hidden="true"></i>
        Baixar meus dados
    </x-primary-link>
</section>
