<x-guest-layout>
    <div x-data="{ accepted: false, submitting: false }" class="w-full">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-file-contract text-brand-600 dark:text-brand-400 text-xl" aria-hidden="true"></i>
            </div>
            <h2 class="text-xl font-bold text-surface-900 dark:text-white">Termos de Uso</h2>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Para continuar, leia e aceite os termos abaixo</p>
        </div>

        <!-- Terms Content -->
        <div class="relative mb-5">
            <div role="document" aria-label="Termos de Uso do Clientify360" tabindex="0"
                 class="max-h-64 overflow-y-auto border border-surface-200 dark:border-surface-700 rounded-xl p-4 text-sm text-surface-600 dark:text-surface-400 leading-relaxed scrollbar-thin focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">1. Aceitação dos Termos</h3>
                <p class="mb-3">Ao utilizar o Clientify360, você concorda com estes Termos de Uso. Caso não concorde, não utilize a plataforma.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">2. Descrição do Serviço</h3>
                <p class="mb-3">O Clientify360 é uma plataforma de gestão de clientes (CRM) no formato Micro SaaS, que permite o cadastro, organização e gerenciamento de contatos de forma segura e individual.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">3. Conta do Usuário</h3>
                <p class="mb-3">Cada usuário é responsável por manter a confidencialidade de suas credenciais de acesso. Todos os dados cadastrados são de responsabilidade do respectivo usuário.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">4. Privacidade e Proteção de Dados (LGPD)</h3>
                <p class="mb-3">Seus dados são protegidos conforme a Lei Geral de Proteção de Dados (LGPD). Cada usuário acessa apenas seus próprios dados. Não compartilhamos informações com terceiros sem consentimento explícito.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">5. Planos e Pagamentos</h3>
                <p class="mb-3">A plataforma oferece planos gratuito e pago. O plano gratuito possui limitações de uso. Assinaturas pagas são gerenciadas via Stripe e podem ser canceladas a qualquer momento.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">6. Uso Adequado</h3>
                <p class="mb-3">O usuário se compromete a não utilizar a plataforma para fins ilegais, spam, armazenamento de dados sensíveis sem autorização, ou qualquer atividade que viole a legislação vigente.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">7. Exclusão de Dados</h3>
                <p class="mb-3">O usuário pode solicitar a exclusão total de seus dados a qualquer momento, conforme previsto na LGPD. A exclusão é irreversível.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">8. Limitação de Responsabilidade</h3>
                <p class="mb-3">O Clientify360 não se responsabiliza por perdas de dados causadas por uso inadequado, falhas de rede externas, ou ações de terceiros.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">9. Alterações nos Termos</h3>
                <p class="mb-3">Reservamo-nos o direito de atualizar estes termos. Alterações significativas serão comunicadas e exigirão novo aceite.</p>

                <h3 class="font-bold text-surface-800 dark:text-surface-200 mb-2">10. Contato</h3>
                <p>Em caso de dúvidas, entre em contato através da página de contato da plataforma ou pelo site <a href="https://www.3wonline.com.br" target="_blank" rel="noopener noreferrer" class="text-brand-600 dark:text-brand-400 hover:underline">www.3wonline.com.br</a>.</p>
            </div>
            <!-- Scroll hint gradient -->
            <div class="absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-white dark:from-surface-900 to-transparent rounded-b-xl pointer-events-none" aria-hidden="true"></div>
        </div>

        <!-- Accept Form -->
        <form method="POST" action="{{ route('terms.accept') }}" @submit="submitting = true">
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
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-surface-500 dark:text-surface-400 hover:text-danger-600 dark:hover:text-danger-400 transition">
                    <i class="fa-solid fa-right-from-bracket mr-1" aria-hidden="true"></i> Sair da conta
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
