<section id="tokens-api" class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-surface-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-key text-brand-500" aria-hidden="true"></i>
            Tokens de API
            <x-tooltip text="Use um token para integrar o Clientify360 a outros sistemas (ERP, site, planilhas). Cada token só acessa os dados da sua conta e apenas as permissões escolhidas." />
        </h2>

        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
            Envie o token no cabeçalho <code class="px-1 rounded bg-surface-100 dark:bg-surface-800">Authorization: Bearer SEU_TOKEN</code>.
            Exemplo: <code class="px-1 rounded bg-surface-100 dark:bg-surface-800">GET {{ url('/api/v1/clients') }}</code>.
            @if($canCreateTokens)
                Veja todos os endpoints na <a href="{{ url('/api/documentation') }}" class="text-brand-600 dark:text-brand-400 underline" target="_blank" rel="noopener">documentação da API</a>.
            @endif
        </p>
    </header>

    @if(session('plain_token'))
        <div class="rounded-lg border border-accent-300 bg-accent-50 dark:bg-accent-950/30 dark:border-accent-800 p-4 space-y-2" x-data="{ copied: false }">
            <p class="text-sm font-semibold text-accent-800 dark:text-accent-200">
                <i class="fa-solid fa-triangle-exclamation mr-1" aria-hidden="true"></i>
                Copie agora: este token não será exibido novamente.
            </p>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" readonly autocomplete="off" value="{{ session('plain_token') }}" x-ref="token"
                       aria-label="Token de API gerado"
                       class="flex-1 font-mono text-xs rounded-lg border-surface-300 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-100">
                <button type="button" class="c-btn-ghost"
                        @click="navigator.clipboard.writeText($refs.token.value).then(() => copied = true)">
                    <i class="fa-solid fa-copy mr-1" aria-hidden="true"></i>
                    <span x-text="copied ? 'Copiado!' : 'Copiar'">Copiar</span>
                </button>
            </div>
        </div>
    @endif

    @if(!$canCreateTokens)
        <div class="rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 p-4 text-sm text-amber-800 dark:text-amber-200">
            <i class="fa-solid fa-lock mr-1" aria-hidden="true"></i>
            Tokens inativos no plano Básico. Você ainda pode revogá-los; para usá-los novamente, assine o Profissional.
            @can('account.billing')
                <a href="{{ route('billing.index') }}" class="font-semibold underline ml-1">Ver planos</a>
            @else
                <span class="ml-1">Fale com o dono da conta.</span>
            @endcan
        </div>
    @else
        <form method="POST" action="{{ route('profile.tokens.store') }}" autocomplete="off" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="token_name" value="Nome do token" />
                <x-text-input id="token_name" name="name" type="text" class="mt-1 block w-full" maxlength="60" required
                              autocomplete="off" :value="old('name')" placeholder="Ex.: Integração ERP" />
                <x-input-error class="mt-2" :messages="$errors->apiToken->get('name')" />
            </div>

            <fieldset>
                <legend class="text-sm font-medium text-surface-700 dark:text-surface-300">Permissões</legend>
                <p class="text-xs text-surface-500 dark:text-surface-400">Dica: dê ao token só o que a integração precisa.</p>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach(\App\Support\ApiAbilities::LABELS as $ability => $label)
                        <label class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-300">
                            <input type="checkbox" name="abilities[]" value="{{ $ability }}" autocomplete="off"
                                   class="rounded border-surface-300 dark:border-surface-700 text-brand-600"
                                   @checked(in_array($ability, (array) old('abilities', [\App\Support\ApiAbilities::CLIENTS_READ]), true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <x-input-error class="mt-2" :messages="$errors->apiToken->get('abilities')" />
            </fieldset>

            <div>
                <x-input-label for="expires_in_days" value="Validade" />
                <select id="expires_in_days" name="expires_in_days" autocomplete="off"
                        class="mt-1 block w-full sm:w-auto rounded-lg border-surface-300 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-100">
                    @foreach(\App\Services\ApiTokenService::EXPIRATION_DAYS as $days)
                        <option value="{{ $days }}" @selected((string) old('expires_in_days', '90') === (string) $days)>{{ $days }} dias</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->apiToken->get('expires_in_days')" />
            </div>

            <x-primary-button>
                <i class="fa-solid fa-plus mr-2" aria-hidden="true"></i> Criar token
            </x-primary-button>
            <p class="text-xs text-surface-500 dark:text-surface-400">Limite de {{ \App\Services\ApiTokenService::MAX_ACTIVE_TOKENS }} tokens ativos por usuário.</p>
        </form>
    @endif

    <div>
        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-2">Seus tokens</h3>
        @forelse($apiTokens as $token)
            <div class="flex items-start justify-between gap-3 py-3 border-t border-surface-200 dark:border-surface-800">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-surface-900 dark:text-white truncate">{{ $token->name }}</p>
                    <p class="text-xs text-surface-500 dark:text-surface-400">
                        {{ collect($token->abilities)->map(fn ($a) => \App\Support\ApiAbilities::label($a))->join(', ') }}
                    </p>
                    <p class="text-xs text-surface-500 dark:text-surface-400">
                        Criado em {{ $token->created_at?->format('d/m/Y') }}
                        · {{ $token->expires_at ? ($token->expires_at->isPast() ? 'Expirou em ' : 'Expira em ').$token->expires_at->format('d/m/Y') : 'Sem expiração' }}
                        · {{ $token->last_used_at ? 'Último uso em '.$token->last_used_at->format('d/m/Y H:i') : 'Nunca usado' }}
                    </p>
                </div>
                <x-confirm-delete :action="route('profile.tokens.destroy', $token->id)"
                                  title="Revogar este token?"
                                  text="As integrações que usam este token deixarão de funcionar imediatamente."
                                  confirm="Revogar" label="Revogar token" />
            </div>
        @empty
            <p class="text-sm text-surface-500 dark:text-surface-400">Nenhum token criado.</p>
        @endforelse
    </div>
</section>
