<x-app-layout>
    @section('title', 'Histórico de pagamentos')

    <x-slot name="header">
        <x-breadcrumb :items="[
            ['label' => 'Financeiro', 'url' => auth()->user()->can('finance.view') ? route('finance.index') : null],
            ['label' => 'Histórico de pagamentos'],
        ]" />
    </x-slot>

    @php
        $statusClass = fn (string $status): string => match ($status) {
            'paid' => 'bg-accent-50 dark:bg-accent-950/50 text-accent-700 dark:text-accent-300 border-accent-200 dark:border-accent-800',
            'open', 'draft' => 'bg-warning-50 dark:bg-warning-950/50 text-warning-700 dark:text-warning-300 border-warning-200 dark:border-warning-800',
            default => 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400 border-surface-200 dark:border-surface-700',
        };
    @endphp

    <x-page-title help-description="Faturas da assinatura emitidas pela Stripe." help-example="Baixe o PDF da fatura de maio para a contabilidade." help-tip="Use &#039;Ver recibo&#039; para a página oficial de pagamento." title="Histórico de pagamentos" icon="fa-receipt" subtitle="Todas as faturas da sua assinatura, com PDF e recibo emitidos pelo Stripe.">
        <x-slot name="actions">
            <x-tooltip position="left" text="As faturas são geradas pelo Stripe a cada renovação mensal. Use “Baixar PDF” para a fatura e “Ver recibo” para a página oficial de pagamento." />
            <a href="{{ route('billing.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-700 dark:text-surface-300 text-sm font-medium rounded-xl transition">
                <i class="fa-solid fa-credit-card mr-2 text-xs"></i> Minha assinatura
            </a>
        </x-slot>
    </x-page-title>

    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
        @if($invoicesError)
            <div class="p-8 text-center" role="alert">
                <i class="fa-solid fa-triangle-exclamation text-3xl text-warning-500 mb-3"></i>
                <p class="text-sm font-semibold text-surface-800 dark:text-surface-200">Não foi possível carregar o histórico agora. Tente novamente em instantes.</p>
                <a href="{{ route('billing.invoices') }}" class="mt-4 inline-flex items-center text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">
                    <i class="fa-solid fa-rotate-right mr-1.5"></i> Tentar novamente
                </a>
            </div>
        @elseif($invoices->isEmpty())
            <x-empty-state icon="fa-receipt" title="Nenhum pagamento registrado" text="O histórico aparece aqui depois da sua primeira assinatura do plano Profissional." />
        @else
            {{-- Desktop: tabela --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="c-table-stack min-w-full">
                    <thead>
                        <tr class="border-b border-surface-200 dark:border-surface-800">
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Valor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Situação</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                        @foreach($invoices as $invoice)
                            <tr>
                                <td data-label="Data" class="px-6 py-4 whitespace-nowrap text-sm text-surface-700 dark:text-surface-300">{{ $invoice->date->format('d/m/Y') }}</td>
                                <td data-label="Valor" class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-surface-900 dark:text-white">{{ $invoice->total }}</td>
                                <td data-label="Situação" class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClass($invoice->status) }}">{{ $invoice->statusLabel }}</span>
                                </td>
                                <td data-label="Ações" class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                    @include('billing.partials.invoice-links', ['invoice' => $invoice])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: cartões --}}
            <ul class="md:hidden divide-y divide-surface-100 dark:divide-surface-800">
                @foreach($invoices as $invoice)
                    <li class="p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500 dark:text-surface-400">{{ $invoice->date->format('d/m/Y') }}</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClass($invoice->status) }}">{{ $invoice->statusLabel }}</span>
                        </div>
                        <p class="mt-1 text-lg font-bold text-surface-900 dark:text-white">{{ $invoice->total }}</p>
                        <div class="mt-2 text-sm space-x-3">
                            @include('billing.partials.invoice-links', ['invoice' => $invoice])
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
