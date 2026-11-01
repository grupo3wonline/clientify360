{{-- "Privacidade e dados": the data subject rights available in the app (LGPD, GDPR, CCPA/CPRA, POPIA). --}}
<section class="space-y-6" aria-labelledby="privacy-data-title">
    <header>
        <h2 id="privacy-data-title" class="text-lg font-medium text-surface-900 dark:text-white">
            Privacidade e dados
        </h2>

        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
            Exerça aqui os seus direitos de titular: acesso, portabilidade, correção, exclusão e escolha de cookies. Veja os detalhes na <a href="{{ route('privacy') }}#como-exercer" class="text-brand-600 dark:text-brand-400 underline underline-offset-2 hover:no-underline">Política de Privacidade</a>.
        </p>
    </header>

    <div>
        <h3 class="text-sm font-semibold text-surface-900 dark:text-white">Baixar meus dados</h3>
        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
            Baixe uma cópia dos seus dados pessoais em formato JSON: identificação, consentimentos e o seu papel na conta (direito de acesso e portabilidade).
        </p>
        <div class="mt-3 rounded-lg bg-surface-50 dark:bg-surface-800/50 border border-surface-200 dark:border-surface-700 p-4">
            <p class="text-sm text-surface-700 dark:text-surface-300 flex items-start gap-2">
                <i class="fa-solid fa-circle-info mt-0.5 text-brand-500" aria-hidden="true"></i>
                <span><strong>Dica:</strong> o arquivo contém apenas os seus dados. Nenhuma informação de outros usuários, senha ou chave de acesso é incluída. Guarde-o em local seguro.</span>
            </p>
        </div>
        <x-primary-link
            :href="route('profile.export')"
            data-no-loading
            class="mt-3 w-full sm:w-auto gap-2"
            aria-label="Baixar uma cópia dos seus dados pessoais em formato JSON"
            title="Baixar uma cópia dos seus dados pessoais em formato JSON">
            <i class="fa-solid fa-download" aria-hidden="true"></i>
            Baixar meus dados
        </x-primary-link>
    </div>

    <ul class="grid gap-3 sm:grid-cols-2">
        <li>
            <a href="#dados-do-perfil" class="flex h-full items-start gap-3 rounded-xl border border-surface-200 dark:border-surface-700 p-4 hover:border-brand-300 dark:hover:border-brand-700 transition">
                <i class="fa-solid fa-pen-to-square mt-0.5 text-brand-500" aria-hidden="true"></i>
                <span class="text-sm"><span class="block font-semibold text-surface-900 dark:text-white">Corrigir meus dados</span><span class="text-surface-500 dark:text-surface-400">Atualize nome, usuário e e-mail no formulário do perfil.</span></span>
            </a>
        </li>
        <li>
            <button type="button" data-cookie-preferences class="flex h-full w-full items-start gap-3 rounded-xl border border-surface-200 dark:border-surface-700 p-4 text-left hover:border-brand-300 dark:hover:border-brand-700 transition">
                <i class="fa-solid fa-cookie-bite mt-0.5 text-brand-500" aria-hidden="true"></i>
                <span class="text-sm"><span class="block font-semibold text-surface-900 dark:text-white">Preferências de cookies</span><span class="text-surface-500 dark:text-surface-400">Ative ou desative os cookies de análise e de marketing.</span></span>
            </button>
        </li>
        <li>
            <a href="#excluir-conta" class="flex h-full items-start gap-3 rounded-xl border border-surface-200 dark:border-surface-700 p-4 hover:border-danger-300 dark:hover:border-danger-700 transition">
                <i class="fa-solid fa-user-xmark mt-0.5 text-danger-500" aria-hidden="true"></i>
                <span class="text-sm"><span class="block font-semibold text-surface-900 dark:text-white">Excluir minha conta</span><span class="text-surface-500 dark:text-surface-400">Encerra o seu acesso. Exige a confirmação da senha.</span></span>
            </a>
        </li>
        <li>
            <a href="mailto:{{ config('legal.controller.dpo_email') }}" class="flex h-full items-start gap-3 rounded-xl border border-surface-200 dark:border-surface-700 p-4 hover:border-brand-300 dark:hover:border-brand-700 transition">
                <i class="fa-solid fa-envelope mt-0.5 text-brand-500" aria-hidden="true"></i>
                <span class="text-sm"><span class="block font-semibold text-surface-900 dark:text-white">Outros pedidos</span><span class="text-surface-500 dark:text-surface-400">Fale com o Encarregado (DPO): {{ config('legal.controller.dpo_email') }}</span></span>
            </a>
        </li>
    </ul>

    <p class="text-sm text-surface-600 dark:text-surface-400">
        Você pode retirar o consentimento de cookies opcionais a qualquer momento. O consentimento dado no cadastro para tratamento dos dados da conta é necessário para prestar o serviço; para retirá-lo, exclua sua conta.
    </p>
</section>
