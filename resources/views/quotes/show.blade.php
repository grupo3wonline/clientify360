<x-app-layout>
    @section('title', "Orçamento {$quote->number}")

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Orçamentos', 'url' => route('quotes.index')], ['label' => $quote->number]]" />
    </x-slot>

    <x-page-title :title="$quote->number . ' · ' . $quote->title" icon="fa-file-invoice-dollar">
        <x-slot name="actions">
            <span class="c-badge text-xs px-3 py-1 {{ $quote->statusColor() }}">{{ $quote->statusLabel() }}</span>
            <a href="{{ route('quotes.print', $quote) }}" target="_blank" class="c-btn-ghost"><i class="fa-solid fa-print mr-2 text-xs"></i> Imprimir</a>
            <x-pdf-button :href="route('quotes.pdf', $quote)" />
            @if($quote->isEditable())
                <a href="{{ route('quotes.edit', $quote) }}" class="c-btn-ghost"><i class="fa-solid fa-pen mr-2 text-xs"></i> Editar</a>
            @endif
            <form autocomplete="off" action="{{ route('quotes.duplicate', $quote) }}" method="POST">@csrf<button class="c-btn-ghost" title="Duplicar"><i class="fa-regular fa-copy"></i></button></form>
        </x-slot>
    </x-page-title>

    @if($quote->isEditable())
        <div class="c-card p-4 mb-6 flex flex-col md:flex-row md:items-center gap-3 bg-gradient-to-r from-brand-50 to-white dark:from-brand-950/40 dark:to-surface-900">
            <p class="text-sm flex-1"><i class="fa-solid fa-route text-brand-500 mr-2"></i>
                @if($quote->status === 'draft') Revise e marque como <strong>enviado</strong> quando mandar ao cliente. @else Aguardando resposta do cliente. @endif
            </p>
            <div class="flex flex-wrap gap-2">
                @if($quote->status === 'draft')
                    <form autocomplete="off" action="{{ route('quotes.send', $quote) }}" method="POST">@csrf<button class="c-btn-ghost"><i class="fa-solid fa-paper-plane mr-2 text-xs"></i> Marcar como enviado</button></form>
                @endif
                <x-confirm-delete :action="route('quotes.reject', $quote)" method="POST" title="Cliente recusou?" text="O orçamento ficará como recusado." confirm="Recusado" class="c-btn-ghost text-danger-600">
                    <i class="fa-solid fa-xmark mr-2 text-xs"></i> Recusado
                </x-confirm-delete>
                <button type="button" x-data @click="$dispatch('open-modal', 'approve')" class="c-btn-success"><i class="fa-solid fa-check-double mr-2 text-xs"></i> Aprovar e gerar venda</button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 c-card overflow-hidden h-fit">
            <div class="overflow-x-auto">
                <table class="c-table-stack min-w-full divide-y divide-surface-100 dark:divide-surface-800">
                    <thead class="bg-surface-50 dark:bg-surface-800/50"><tr><th class="c-th">Item</th><th class="c-th text-right">Qtd.</th><th class="c-th text-right">Unitário</th><th class="c-th text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                        @foreach($quote->items as $item)
                            <tr>
                                <td data-label="Item" class="c-td">{{ $item->description }}
                                    @if($item->product?->track_stock)<span class="text-xs text-surface-400 ml-1" title="Saldo atual em estoque">(estoque: @qty($item->product->stock))</span>@endif
                                </td>
                                <td data-label="Qtd." class="c-td text-right">@qty($item->quantity)</td>
                                <td data-label="Unitário" class="c-td text-right whitespace-nowrap">@money($item->unit_price)</td>
                                <td data-label="Total" class="c-td text-right font-semibold whitespace-nowrap">@money($item->total)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="text-sm">
                        <tr><td colspan="3" class="c-td text-right text-surface-500">Subtotal</td><td class="c-td text-right">@money($quote->subtotal)</td></tr>
                        @if((float) $quote->discount > 0)<tr><td colspan="3" class="c-td text-right text-surface-500">Desconto</td><td class="c-td text-right text-danger-600">− @money($quote->discount)</td></tr>@endif
                        <tr><td colspan="3" class="c-td text-right font-bold">Total</td><td class="c-td text-right text-lg font-bold text-accent-700 dark:text-accent-400 whitespace-nowrap">@money($quote->total)</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="c-card p-5 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-surface-500">Cliente</span><a href="{{ route('clients.show', $quote->client_id) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $quote->client?->name }}</a></div>
                @if($quote->deal)<div class="flex justify-between"><span class="text-surface-500">Oportunidade</span><a href="{{ route('deals.show', $quote->deal) }}" class="hover:underline truncate ml-3">{{ $quote->deal->title }}</a></div>@endif
                <div class="flex justify-between"><span class="text-surface-500">Emitido em</span><span>{{ $quote->created_at->format('d/m/Y') }}</span></div>
                <div class="flex justify-between"><span class="text-surface-500">Válido até</span><span class="{{ $quote->isExpired() ? 'text-danger-600 font-semibold' : '' }}">{{ $quote->valid_until?->format('d/m/Y') ?? '—' }}</span></div>
                @if($quote->approved_at)<div class="flex justify-between"><span class="text-surface-500">Aprovado em</span><span>{{ $quote->approved_at->format('d/m/Y H:i') }}</span></div>@endif
                @if($quote->payment_terms)<div class="pt-3 border-t border-surface-100 dark:border-surface-800"><p class="text-surface-500 mb-1">Condições</p><p class="whitespace-pre-line">{{ $quote->payment_terms }}</p></div>@endif
                @if($quote->notes)<div class="pt-3 border-t border-surface-100 dark:border-surface-800"><p class="text-surface-500 mb-1">Observações</p><p class="whitespace-pre-line">{{ $quote->notes }}</p></div>@endif
            </div>

            @if($quote->entries->isNotEmpty())
                <div class="c-card p-5">
                    <h3 class="text-sm font-semibold mb-3"><i class="fa-solid fa-money-bill-transfer text-brand-500 mr-2"></i>Contas a receber geradas</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach($quote->entries->sortBy('due_date') as $e)
                            <li class="flex justify-between items-center"><span>{{ $e->due_date->format('d/m/Y') }}</span><span class="flex items-center gap-2">@money($e->amount)<span class="c-badge {{ $e->statusColor() }}">{{ $e->statusLabel() }}</span></span></li>
                        @endforeach
                    </ul>
                    <a href="{{ route('finance.entries', ['direction' => 'receivable']) }}" class="block mt-3 text-xs text-brand-600 dark:text-brand-400 hover:underline">Ver no financeiro →</a>
                </div>
            @endif

            @if($quote->status !== 'approved')
                <div class="text-right"><x-confirm-delete :action="route('quotes.destroy', $quote)" title="Excluir orçamento?" class="text-xs text-danger-600 hover:underline">Excluir orçamento</x-confirm-delete></div>
            @endif
        </div>
    </div>

    @if($quote->isEditable())
        <x-modal name="approve" :show="$errors->hasAny(['installments', 'first_due_date'])" maxWidth="md">
            <form autocomplete="off" action="{{ route('quotes.approve', $quote) }}" method="POST" class="p-6 space-y-4" x-data="{ n: {{ old('installments', 1) }} }">
                @csrf
                <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Aprovar e gerar venda</h2>
                <ul class="text-sm text-surface-600 dark:text-surface-400 space-y-1 list-disc pl-5">
                    <li>Baixa o estoque dos produtos com controle de estoque;</li>
                    <li>Gera as contas a receber de <strong>@money($quote->total)</strong>;</li>
                    @if($quote->deal)<li>Marca a oportunidade como <strong>ganha</strong>;</li>@endif
                    <li>Registra no histórico do cliente.</li>
                </ul>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="c-label">Parcelas</label><input autocomplete="off" type="number" name="installments" min="1" max="60" x-model.number="n" class="c-input"></div>
                    <div><label class="c-label" x-text="n > 1 ? '1º vencimento' : 'Vencimento'"></label><input autocomplete="off" type="date" name="first_due_date" value="{{ old('first_due_date', now()->format('Y-m-d')) }}" class="c-input"></div>
                </div>
                <p class="c-hint" x-show="n > 1" x-text="n + 'x de ' + ({{ (float) $quote->total }} / n).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) + ' (mensais)'"></p>
                <div>
                    <label class="c-label">Forma de pagamento</label>
                    <select autocomplete="off" name="payment_method" class="c-input"><option value="">—</option>@foreach($paymentMethods as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach</select>
                </div>
                <label class="flex items-start gap-2 text-xs text-surface-500 cursor-pointer">
                    <input autocomplete="off" type="checkbox" name="allow_negative_stock" value="1" class="mt-0.5 rounded border-surface-300 text-brand-600">
                    Permitir mesmo com estoque insuficiente (o saldo ficará negativo — útil para venda sob encomenda).
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" x-on:click="$dispatch('close')" class="c-btn-ghost">Cancelar</button>
                    <button class="c-btn-success"><i class="fa-solid fa-check-double mr-2 text-xs"></i> Confirmar venda</button>
                </div>
            </form>
        </x-modal>
    @endif
</x-app-layout>
