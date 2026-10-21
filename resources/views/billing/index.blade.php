<x-app-layout>
    @section('title', 'Assinatura')

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Assinatura</span>
        </nav>
    </x-slot>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Assinatura</h1>
        <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Gerencie o plano, a vigência e o histórico de pagamentos da sua conta</p>
    </div>

    <!-- Current Plan Card -->
    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden mb-6">
        <div class="p-6 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-crown text-white text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Plano Atual</p>
                            <p class="text-xl font-bold text-surface-900 dark:text-white">{{ $currentPlan['name'] }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mt-2">
                        @if($currentPlan['price'] > 0)
                            <span class="text-2xl font-bold text-surface-900 dark:text-white">R$ {{ number_format($currentPlan['price'], 2, ',', '.') }}</span>
                            <span class="text-surface-400"> / mês</span>
                        @else
                            <span class="text-2xl font-bold text-surface-900 dark:text-white">Gratuito</span>
                        @endif
                    </p>
                </div>

                <div>
                    @if($status === 'active')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-accent-50 dark:bg-accent-950/50 text-accent-700 dark:text-accent-300 border border-accent-200 dark:border-accent-800">
                            <span class="w-1.5 h-1.5 bg-accent-500 rounded-full mr-2 animate-pulse"></span> Ativo
                        </span>
                    @elseif($status === 'canceling')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-warning-50 dark:bg-warning-950/50 text-warning-700 dark:text-warning-300 border border-warning-200 dark:border-warning-800">
                            <i class="fa-solid fa-clock mr-1.5 text-[10px]"></i> Cancelando
                        </span>
                    @elseif($status === 'canceled')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-danger-50 dark:bg-danger-950/50 text-danger-700 dark:text-danger-300 border border-danger-200 dark:border-danger-800">
                            <i class="fa-solid fa-circle-xmark mr-1.5 text-[10px]"></i> Cancelado
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400 border border-surface-200 dark:border-surface-700">
                            <i class="fa-solid fa-gift mr-1.5 text-[10px]"></i> Plano Gratuito
                        </span>
                    @endif
                </div>
            </div>

            <!-- Renewal Info -->
            @if($renewalDate && $status === 'active')
            <div class="mt-6 pt-6 border-t border-surface-200 dark:border-surface-800">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-sm text-surface-600 dark:text-surface-400">
                        <i class="fa-solid fa-calendar-days mr-1.5 text-brand-500"></i>
                        Próxima renovação: <strong class="text-surface-800 dark:text-surface-200">{{ $renewalDate->format('d/m/Y') }}</strong>
                    </p>
                    @if($daysUntilRenewal !== null)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium {{ $daysUntilRenewal <= 3 ? 'bg-danger-50 dark:bg-danger-950/50 text-danger-700 dark:text-danger-300 border border-danger-200 dark:border-danger-800' : ($daysUntilRenewal <= 7 ? 'bg-warning-50 dark:bg-warning-950/50 text-warning-700 dark:text-warning-300 border border-warning-200 dark:border-warning-800' : 'bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800') }}">
                        <i class="fa-solid fa-hourglass-half mr-1.5 text-[10px]"></i>
                        {{ $daysUntilRenewal }} {{ $daysUntilRenewal === 1 ? 'dia' : 'dias' }} para renovação
                    </span>
                    @endif
                </div>
            </div>
            @endif

            @if($status === 'canceling' && $renewalDate)
            <div class="mt-6 pt-6 border-t border-surface-200 dark:border-surface-800">
                <div class="flex items-start space-x-3 bg-warning-50 dark:bg-warning-950/30 rounded-xl p-4 border border-warning-200 dark:border-warning-800/50">
                    <i class="fa-solid fa-triangle-exclamation text-warning-500 mt-0.5"></i>
                    <p class="text-sm text-warning-800 dark:text-warning-200">
                        Sua assinatura será encerrada em <strong>{{ $renewalDate->format('d/m/Y') }}</strong>. Após essa data, você voltará ao plano Básico.
                    </p>
                </div>
            </div>
            @endif

            <!-- Plan Actions -->
            <div class="mt-6 pt-6 border-t border-surface-200 dark:border-surface-800 flex flex-wrap gap-3">
                @if($status === 'free')
                    <form action="{{ route('billing.subscribe') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25 hover:shadow-brand-600/40">
                            <i class="fa-solid fa-rocket mr-2 text-xs"></i> Fazer Upgrade para Profissional
                        </button>
                    </form>
                @elseif($status === 'active')
                    <form action="{{ route('billing.cancel') }}" method="POST" x-data>
                        @csrf
                        <button type="button" @click="Swal.fire({ title: 'Cancelar assinatura?', text: 'Você terá acesso até o final do período atual.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Sim, cancelar', cancelButtonText: 'Manter assinatura', customClass: { popup: 'rounded-2xl' } }).then((result) => { if (result.isConfirmed) $el.closest('form').submit() })"
                                class="inline-flex items-center px-5 py-2.5 bg-danger-600 hover:bg-danger-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                            <i class="fa-solid fa-ban mr-2 text-xs"></i> Cancelar Assinatura
                        </button>
                    </form>
                    <a href="{{ route('billing.portal') }}" class="inline-flex items-center px-5 py-2.5 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-700 dark:text-surface-300 text-sm font-medium rounded-xl transition">
                        <i class="fa-solid fa-gear mr-2 text-xs"></i> Gerenciar no Stripe
                    </a>
                @elseif($status === 'canceling')
                    <form action="{{ route('billing.resume') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-accent-600 hover:bg-accent-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-accent-600/25">
                            <i class="fa-solid fa-rotate-left mr-2 text-xs"></i> Reativar Assinatura
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Plans Comparison -->
    <div class="mb-6">
        <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-4 flex items-center">
            <i class="fa-solid fa-table-columns text-brand-500 mr-2"></i> Comparação de Planos
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($plans as $slug => $plan)
            <div class="relative bg-white dark:bg-surface-900 rounded-2xl border {{ $currentPlan['slug'] === $slug ? 'border-brand-300 dark:border-brand-700 ring-2 ring-brand-500/20' : 'border-surface-200 dark:border-surface-800' }} overflow-hidden hover:shadow-lg hover:shadow-surface-200/50 dark:hover:shadow-none transition-shadow">
                @if($currentPlan['slug'] === $slug)
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-500 to-brand-600"></div>
                @endif

                <div class="p-6">
                    @if($currentPlan['slug'] === $slug)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800 mb-3">
                        <i class="fa-solid fa-check-circle mr-1"></i> Seu Plano
                    </span>
                    @endif

                    <h4 class="text-xl font-bold text-surface-900 dark:text-white">{{ $plan['name'] }}</h4>

                    <div class="mt-3 mb-5">
                        @if($plan['price'] > 0)
                            <span class="text-3xl font-bold text-surface-900 dark:text-white">R$ {{ number_format($plan['price'], 2, ',', '.') }}</span>
                            <span class="text-sm text-surface-400 dark:text-surface-500">/mês</span>
                        @else
                            <span class="text-3xl font-bold text-surface-900 dark:text-white">Grátis</span>
                            <span class="text-sm text-surface-400 dark:text-surface-500">para sempre</span>
                        @endif
                    </div>

                    <ul class="space-y-2.5 mb-6">
                        @foreach($plan['features'] as $feature)
                        <li class="flex items-center text-sm text-surface-600 dark:text-surface-400">
                            <i class="fa-solid fa-circle-check text-accent-500 mr-2.5 text-xs"></i>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>

                    @if($currentPlan['slug'] !== $slug && $slug === 'professional' && $status === 'free')
                    <form action="{{ route('billing.subscribe') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25 hover:shadow-brand-600/40">
                            <i class="fa-solid fa-arrow-up mr-2 text-xs"></i> Fazer Upgrade
                        </button>
                    </form>
                    @elseif($currentPlan['slug'] === $slug)
                    <div class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400 text-sm font-medium rounded-xl cursor-default">
                        <i class="fa-solid fa-check mr-2 text-xs"></i> Plano Atual
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Payment History -->
    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
        <div class="p-6 md:p-8">
            <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-5 flex items-center">
                <i class="fa-solid fa-clock-rotate-left text-brand-500 mr-2"></i> Histórico de Pagamentos
            </h3>

            @if(count($paymentHistory) > 0)
            <div class="overflow-x-auto -mx-6 md:-mx-8">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-surface-200 dark:border-surface-800">
                            <th scope="col" class="px-6 md:px-8 py-3 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Valor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 md:px-8 py-3 text-center text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Recibo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                        @foreach($paymentHistory as $payment)
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                            <td class="px-6 md:px-8 py-4 whitespace-nowrap text-sm text-surface-700 dark:text-surface-300">
                                {{ $payment['date'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-surface-900 dark:text-white">
                                {{ $payment['amount'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($payment['status'] === 'Pago')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-accent-50 dark:bg-accent-950/50 text-accent-700 dark:text-accent-300 border border-accent-200 dark:border-accent-800">
                                    <i class="fa-solid fa-check mr-1"></i> Pago
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-warning-50 dark:bg-warning-950/50 text-warning-700 dark:text-warning-300 border border-warning-200 dark:border-warning-800">
                                    <i class="fa-solid fa-clock mr-1"></i> Pendente
                                </span>
                                @endif
                            </td>
                            <td class="px-6 md:px-8 py-4 whitespace-nowrap text-center">
                                @if($payment['pdf_url'])
                                <a href="{{ $payment['pdf_url'] }}" target="_blank" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-950/30 rounded-lg transition">
                                    <i class="fa-solid fa-file-pdf mr-1.5"></i> PDF
                                </a>
                                @else
                                <span class="text-surface-300 dark:text-surface-600">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-10">
                <div class="w-14 h-14 bg-surface-100 dark:bg-surface-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-receipt text-xl text-surface-400 dark:text-surface-500"></i>
                </div>
                <h4 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-1">Nenhum pagamento registrado</h4>
                <p class="text-xs text-surface-500 dark:text-surface-400">
                    O histórico aparecerá aqui após sua primeira assinatura.
                </p>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    @if(request()->query('checkout') === 'success')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Parabéns! 🎉',
                text: 'Sua assinatura do plano Profissional foi ativada com sucesso!',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#4f46e5',
                customClass: { popup: 'rounded-2xl' }
            });
        });
    </script>
    @endif
    @if(request()->query('checkout') === 'cancel')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Checkout cancelado',
                text: 'Você pode tentar novamente quando quiser.',
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#4f46e5',
                customClass: { popup: 'rounded-2xl' }
            });
        });
    </script>
    @endif
    @endpush
</x-app-layout>
