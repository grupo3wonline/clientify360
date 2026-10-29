<x-app-layout>
    @section('title', 'Minha assinatura')

    <x-slot name="header">
        <x-breadcrumb :items="[
            ['label' => 'Financeiro', 'url' => auth()->user()->can('finance.view') ? route('finance.index') : null],
            ['label' => 'Minha assinatura'],
        ]" />
    </x-slot>

    @php
        $plan = $summary->plan;
        $countdownEnd = $summary->countdownEnd();
        $badge = match ($summary->status) {
            'active' => 'bg-accent-50 dark:bg-accent-950/50 text-accent-700 dark:text-accent-300 border-accent-200 dark:border-accent-800',
            'canceling', 'past_due', 'incomplete' => 'bg-warning-50 dark:bg-warning-950/50 text-warning-700 dark:text-warning-300 border-warning-200 dark:border-warning-800',
            'canceled' => 'bg-danger-50 dark:bg-danger-950/50 text-danger-700 dark:text-danger-300 border-danger-200 dark:border-danger-800',
            default => 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400 border-surface-200 dark:border-surface-700',
        };
        $exceeded = array_filter($usage, fn (array $row): bool => $row['limit'] !== -1 && $row['usage'] >= $row['limit']);
    @endphp

    <x-page-title help-description="Seu plano, a vigência e as opções de troca de plano." help-example="Assine o Profissional para liberar IA, PDF, exportação e API." help-tip="Ao cancelar, você mantém o Profissional até o fim do período pago." title="Minha assinatura" icon="fa-credit-card" subtitle="Seu plano atual, a vigência da assinatura e as opções de troca de plano.">
        <x-slot name="actions">
            <x-tooltip position="left" text="A assinatura é mensal e renovada automaticamente. Ao cancelar, você mantém o Profissional até o fim do período pago. Se um pagamento falhar, a conta passa a usar os limites do Básico até a regularização.">
                <span class="inline-flex items-center gap-1.5 text-sm text-brand-600 dark:text-brand-400">
                    <i class="fa-solid fa-circle-question"></i> Como funciona a cobrança?
                </span>
            </x-tooltip>
            <a href="{{ route('billing.invoices') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-700 dark:text-surface-300 text-sm font-medium rounded-xl transition">
                <i class="fa-solid fa-receipt mr-2 text-xs"></i> Histórico de pagamentos
            </a>
        </x-slot>
    </x-page-title>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Plano atual e vigência --}}
        <section class="lg:col-span-2 bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6" aria-labelledby="current-plan-title">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-crown text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Plano atual</p>
                        <h2 id="current-plan-title" class="text-xl font-bold text-surface-900 dark:text-white">{{ $plan->name }}</h2>
                        <p class="text-sm text-surface-500 dark:text-surface-400">
                            @if($plan->isFree())
                                Gratuito para sempre
                            @else
                                <strong class="text-surface-900 dark:text-white">{{ $plan->formattedPrice() }}</strong> / mês
                            @endif
                        </p>
                    </div>
                </div>
                <span class="self-start inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border {{ $badge }}">
                    {{ $summary->statusLabel }}
                </span>
            </div>

            <dl class="mt-6 pt-6 border-t border-surface-200 dark:border-surface-800 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-surface-500 dark:text-surface-400">Início do período</dt>
                    <dd class="font-semibold text-surface-900 dark:text-white">{{ $summary->periodStart?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-surface-500 dark:text-surface-400">{{ $summary->cancelAtPeriodEnd ? 'Acesso até' : 'Próxima renovação' }}</dt>
                    <dd class="font-semibold text-surface-900 dark:text-white">{{ $countdownEnd?->format('d/m/Y') ?? '—' }}</dd>
                </div>
            </dl>

            @if($summary->degraded)
                <p class="mt-4 text-sm text-warning-700 dark:text-warning-300">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Não foi possível consultar as datas do período agora.
                </p>
            @endif

            @if($summary->cancelAtPeriodEnd && $countdownEnd)
                <div class="mt-4 flex items-start gap-3 bg-warning-50 dark:bg-warning-950/30 rounded-xl p-4 border border-warning-200 dark:border-warning-800/50">
                    <i class="fa-solid fa-triangle-exclamation text-warning-500 mt-0.5"></i>
                    <p class="text-sm text-warning-800 dark:text-warning-200">
                        Sua assinatura termina em <strong>{{ $countdownEnd->format('d/m/Y') }}</strong>. Depois dessa data a conta volta ao plano Básico; seus dados continuam salvos.
                    </p>
                </div>
            @endif

            @if($summary->status === 'free')
                <p class="mt-4 text-sm text-surface-500 dark:text-surface-400">
                    <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i>
                    Dica: no Profissional você libera clientes ilimitados, IA, exportação e mais. Você pode cancelar quando quiser.
                </p>
            @endif
        </section>

        {{-- Contagem regressiva --}}
        <section class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6" aria-labelledby="countdown-title">
            <h2 id="countdown-title" class="text-sm font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider mb-3">
                <i class="fa-solid fa-hourglass-half text-brand-500 mr-1"></i>
                {{ $summary->cancelAtPeriodEnd ? 'Fim do acesso Profissional' : 'Renovação automática' }}
            </h2>
            @if($countdownEnd)
                <div x-data="countdown(@js($countdownEnd->toIso8601String()))" data-ends-at="{{ $countdownEnd->toIso8601String() }}">
                    <p class="text-2xl font-bold text-surface-900 dark:text-white tabular-nums" x-text="label" aria-live="polite">
                        {{ $countdownEnd->format('d/m/Y') }}
                    </p>
                    <p class="text-xs text-surface-500 dark:text-surface-400 mt-2">Até {{ $countdownEnd->format('d/m/Y H:i') }} (horário de Brasília)</p>
                </div>
            @else
                <p class="text-sm text-surface-500 dark:text-surface-400">
                    {{ $summary->status === 'free' ? 'O plano Básico não tem vencimento.' : 'Sem data de vencimento disponível.' }}
                </p>
            @endif
        </section>
    </div>

    {{-- Gestão de plano --}}
    <section class="mb-6" aria-labelledby="plans-title">
        <h2 id="plans-title" class="text-lg font-bold text-surface-900 dark:text-white mb-4 flex items-center">
            <i class="fa-solid fa-arrows-rotate text-brand-500 mr-2"></i> Trocar de plano
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($plans as $slug => $option)
                @php($isCurrent = $plan->slug === $slug)
                <div class="relative bg-white dark:bg-surface-900 rounded-2xl border {{ $isCurrent ? 'border-brand-300 dark:border-brand-700 ring-2 ring-brand-500/20' : 'border-surface-200 dark:border-surface-800' }} p-6 flex flex-col">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-xl font-bold text-surface-900 dark:text-white">{{ $option->name }}</h3>
                        @if($isCurrent)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                                <i class="fa-solid fa-check-circle mr-1"></i> Plano atual
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">{{ $option->description }}</p>

                    <div class="mt-4 mb-5">
                        @if($option->isFree())
                            <span class="text-3xl font-bold text-surface-900 dark:text-white">Grátis</span>
                            <span class="text-sm text-surface-400">para sempre</span>
                        @else
                            @if($option->discountPercent() > 0)
                                <span class="block text-sm text-surface-400"><s>{{ $option->formattedOriginalPrice() }}</s> · {{ $option->discountPercent() }}% OFF</span>
                            @endif
                            <span class="text-3xl font-bold text-surface-900 dark:text-white">{{ $option->formattedPrice() }}</span>
                            <span class="text-sm text-surface-400">/mês</span>
                        @endif
                    </div>

                    <ul class="space-y-2.5 mb-6 flex-1">
                        @foreach($option->features as $feature)
                            <li class="flex items-start text-sm text-surface-600 dark:text-surface-400">
                                <i class="fa-solid fa-circle-check text-accent-500 mr-2.5 mt-0.5 text-xs"></i> {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    @if($isCurrent)
                        <div class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400 text-sm font-medium rounded-xl">
                            <i class="fa-solid fa-check mr-2 text-xs"></i> Plano atual
                        </div>
                    @elseif(! $option->isFree())
                        <form action="{{ route('billing.subscribe') }}" method="POST" autocomplete="off" data-loading-text="Redirecionando para o pagamento seguro...">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                                <i class="fa-solid fa-rocket mr-2 text-xs"></i> Assinar Profissional por {{ $option->formattedPrice() }}/mês
                            </button>
                        </form>
                    @elseif($summary->cancelAtPeriodEnd)
                        <form action="{{ route('billing.resume') }}" method="POST" autocomplete="off" data-loading-text="Reativando assinatura...">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-accent-600 hover:bg-accent-700 text-white text-sm font-medium rounded-xl transition">
                                <i class="fa-solid fa-rotate-left mr-2 text-xs"></i> Reativar assinatura
                            </button>
                        </form>
                        <p class="text-xs text-center text-surface-400 mt-2">A volta ao Básico já está agendada para {{ $countdownEnd?->format('d/m/Y') ?? 'o fim do período' }}.</p>
                    @else
                        <form action="{{ route('billing.cancel') }}" method="POST" autocomplete="off" x-data>
                            @csrf
                            <button type="button"
                                    @click="Swal.fire({ title: 'Voltar para o Básico?', text: 'Você mantém o Profissional até o fim do período pago. Depois, novos cadastros acima dos limites do Básico ficam bloqueados; seus dados continuam salvos.', icon: 'warning', showCancelButton: true, confirmButtonColor: brandColors.danger['600'], cancelButtonColor: brandColors.surface['500'], confirmButtonText: 'Sim, cancelar ao fim do período', cancelButtonText: 'Manter Profissional' }).then((result) => { if (result.isConfirmed) $el.closest('form').requestSubmit() })"
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-surface-800 border border-danger-300 dark:border-danger-800 text-danger-700 dark:text-danger-300 hover:bg-danger-50 dark:hover:bg-danger-950/30 text-sm font-medium rounded-xl transition">
                                <i class="fa-solid fa-arrow-down mr-2 text-xs"></i> Voltar para o Básico ao fim do período
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        @if(! $plan->isFree())
            <div class="mt-4">
                <a href="{{ route('billing.portal') }}" data-loading-text="Abrindo o portal seguro da Stripe..." class="inline-flex items-center text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">
                    <i class="fa-solid fa-gear mr-1.5 text-xs"></i> Gerenciar pagamento (cartão e dados de cobrança)
                </a>
            </div>
        @endif

        <p class="mt-3 text-xs text-surface-400">
            Assinaturas antigas do Profissional continuam valendo pelo preço contratado até serem canceladas.
        </p>
    </section>

    {{-- Uso do plano --}}
    <section class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6" aria-labelledby="usage-title">
        <h2 id="usage-title" class="text-lg font-bold text-surface-900 dark:text-white mb-1 flex items-center">
            <i class="fa-solid fa-gauge text-brand-500 mr-2"></i> Uso do plano
        </h2>
        <p class="text-sm text-surface-500 dark:text-surface-400 mb-5">Quanto você já usou de cada limite do plano {{ $plan->name }}.</p>

        @foreach($exceeded as $row)
            <div class="mb-4 flex items-start gap-3 bg-warning-50 dark:bg-warning-950/30 rounded-xl p-4 border border-warning-200 dark:border-warning-800/50" role="alert">
                <i class="fa-solid fa-circle-exclamation text-warning-500 mt-0.5"></i>
                <p class="text-sm text-warning-800 dark:text-warning-200">
                    Você está usando {{ $row['usage'] }} de {{ $row['limit'] }} {{ $row['label'] }} permitidos no {{ $plan->name }}. Os registros existentes continuam disponíveis; novos cadastros ficam bloqueados até você reduzir o uso ou voltar ao Profissional.
                </p>
            </div>
        @endforeach

        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($usage as $resource => $row)
                @php($percent = $row['limit'] > 0 ? min(100, (int) round($row['usage'] / $row['limit'] * 100)) : 0)
                <li data-usage="{{ $resource }}">
                    <div class="flex items-center justify-between text-sm mb-1.5">
                        <span class="text-surface-700 dark:text-surface-300 first-letter:uppercase">{{ $row['label'] }}</span>
                        <span class="font-semibold text-surface-900 dark:text-white">
                            {{ $row['limit'] === -1 ? $row['usage'].' (ilimitado)' : $row['usage'].' de '.$row['limit'] }}
                        </span>
                    </div>
                    <div class="h-2 rounded-full bg-surface-100 dark:bg-surface-800 overflow-hidden" role="progressbar" aria-label="Uso de {{ $row['label'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}">
                        @if($row['limit'] === -1)
                            <div class="h-full w-full rounded-full bg-accent-400/50"></div>
                        @else
                            <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-danger-500' : ($percent >= 80 ? 'bg-warning-500' : 'bg-brand-500') }}" style="width: {{ $percent }}%"></div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</x-app-layout>
