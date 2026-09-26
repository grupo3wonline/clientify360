<x-app-layout>
    @section('title', 'Contas a pagar e receber')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Financeiro', 'url' => route('finance.index')], ['label' => 'Lançamentos']]" />
    </x-slot>

    @php
        $prev = $ref->copy()->subMonth()->format('Y-m');
        $next = $ref->copy()->addMonth()->format('Y-m');
        $q = fn (array $extra) => route('finance.entries', array_filter(array_merge(['direction' => $direction, 'status' => $status, 'month' => $ref->format('Y-m')], $extra)));
    @endphp

    <x-page-title title="Contas a pagar e receber" icon="fa-money-bill-transfer">
        <x-slot name="actions">
            <a href="{{ route('finance.categories') }}" class="c-btn-ghost"><i class="fa-solid fa-folder-tree mr-2 text-xs"></i> Categorias</a>
            <button type="button" x-data @click="$dispatch('open-modal', 'new-entry')" class="c-btn-primary"><i class="fa-solid fa-plus mr-2 text-xs"></i> Novo lançamento</button>
        </x-slot>
    </x-page-title>

    <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4">
        <div class="flex items-center gap-1 bg-surface-100 dark:bg-surface-800 rounded-xl p-1 overflow-x-auto">
            <a href="{{ $q(['direction' => null]) }}" class="c-tab {{ !$direction ? 'c-tab-active' : '' }}">Todos</a>
            <a href="{{ $q(['direction' => 'receivable']) }}" class="c-tab {{ $direction === 'receivable' ? 'c-tab-active' : '' }}"><i class="fa-solid fa-arrow-down text-accent-500 mr-1.5 text-xs"></i>A receber</a>
            <a href="{{ $q(['direction' => 'payable']) }}" class="c-tab {{ $direction === 'payable' ? 'c-tab-active' : '' }}"><i class="fa-solid fa-arrow-up text-red-500 mr-1.5 text-xs"></i>A pagar</a>
        </div>
        <div class="flex items-center gap-1 bg-surface-100 dark:bg-surface-800 rounded-xl p-1 overflow-x-auto">
            @foreach([null => 'Qualquer status', 'pending' => 'Pendentes', 'overdue' => 'Atrasados', 'paid' => 'Quitados', 'canceled' => 'Cancelados'] as $k => $label)
                <a href="{{ $q(['status' => $k]) }}" class="c-tab {{ $status == $k ? 'c-tab-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        @if($status !== 'overdue')
            <div class="flex items-center gap-2 lg:ml-auto">
                <a href="{{ $q(['month' => $prev]) }}" class="c-btn-ghost px-3 py-2" aria-label="Mês anterior"><i class="fa-solid fa-chevron-left text-xs"></i></a>
                <span class="text-sm font-semibold min-w-[120px] text-center">{{ ucfirst($ref->translatedFormat('F/Y')) }}</span>
                <a href="{{ $q(['month' => $next]) }}" class="c-btn-ghost px-3 py-2" aria-label="Próximo mês"><i class="fa-solid fa-chevron-right text-xs"></i></a>
            </div>
        @endif
    </div>

    <div class="c-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-800">
                <thead class="bg-surface-50 dark:bg-surface-800/50"><tr><th class="c-th">Vencimento</th><th class="c-th">Descrição</th><th class="c-th hidden md:table-cell">Categoria</th><th class="c-th text-right">Valor</th><th class="c-th">Status</th><th class="c-th text-right">Ações</th></tr></thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                    @forelse($entries as $e)
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/40">
                            <td class="c-td whitespace-nowrap text-xs"><i class="fa-solid {{ $e->direction === 'receivable' ? 'fa-arrow-down text-accent-500' : 'fa-arrow-up text-red-500' }} mr-1.5"></i>{{ $e->due_date->format('d/m/Y') }}</td>
                            <td class="c-td">
                                <p class="font-medium text-surface-900 dark:text-white">{{ $e->description }}</p>
                                <p class="text-xs text-surface-500">
                                    @if($e->client)<a href="{{ route('clients.show', $e->client_id) }}" class="hover:underline">{{ $e->client->name }}</a>@else{{ $e->counterparty }}@endif
                                    @if($e->quote) · <a href="{{ route('quotes.show', $e->quote_id) }}" class="hover:underline">{{ $e->quote->number }}</a>@endif
                                    @if($e->paid_at) · {{ $e->direction === 'receivable' ? 'recebido' : 'pago' }} em {{ $e->paid_at->format('d/m') }}@endif
                                </p>
                            </td>
                            <td class="c-td text-xs hidden md:table-cell">@if($e->category)<span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: {{ $e->category->color }}"></span>{{ $e->category->name }}</span>@else — @endif</td>
                            <td class="c-td text-right font-semibold whitespace-nowrap">@money($e->amount)</td>
                            <td class="c-td"><span class="c-badge {{ $e->statusColor() }}">{{ $e->statusLabel() }}</span></td>
                            <td class="c-td text-right whitespace-nowrap">
                                @if($e->status === 'pending')
                                    <button type="button" x-data @click="$dispatch('open-modal', 'settle-{{ $e->id }}')" class="text-xs font-semibold text-accent-700 dark:text-accent-400 hover:underline mr-2">{{ $e->direction === 'receivable' ? 'Receber' : 'Pagar' }}</button>
                                    <x-confirm-delete :action="route('finance.cancel', $e)" method="POST" title="Cancelar lançamento?" text="Ele sai das previsões, mas fica no histórico." confirm="Cancelar lançamento" color="#64748b" class="p-1.5 text-surface-400 hover:text-amber-600" label="Cancelar"><i class="fa-solid fa-ban text-sm"></i></x-confirm-delete>
                                @else
                                    <form action="{{ route('finance.reopen', $e) }}" method="POST" class="inline">@csrf<button class="p-1.5 text-surface-400 hover:text-brand-600" title="Reabrir"><i class="fa-solid fa-rotate-left text-sm"></i></button></form>
                                @endif
                                <x-confirm-delete :action="route('finance.destroy', $e)" title="Excluir lançamento?" class="p-1.5 text-surface-400 hover:text-red-600" />

                                @if($e->status === 'pending')
                                    <x-modal name="settle-{{ $e->id }}" maxWidth="sm">
                                        <form action="{{ route('finance.settle', $e) }}" method="POST" class="p-6 space-y-4 text-left">
                                            @csrf
                                            <h2 class="text-lg font-semibold text-surface-900 dark:text-white">{{ $e->direction === 'receivable' ? 'Registrar recebimento' : 'Registrar pagamento' }}</h2>
                                            <p class="text-sm text-surface-500 whitespace-normal">{{ $e->description }}</p>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div><label class="c-label">Data</label><input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="c-input"></div>
                                                <div><label class="c-label">Valor</label><input type="number" step="0.01" min="0" name="paid_amount" value="{{ $e->amount }}" class="c-input"></div>
                                            </div>
                                            <div><label class="c-label">Forma</label><select name="payment_method" class="c-input"><option value="">—</option>@foreach($paymentMethods as $k => $label)<option value="{{ $k }}" @selected($e->payment_method === $k)>{{ $label }}</option>@endforeach</select></div>
                                            <div class="flex justify-end gap-2"><button type="button" x-on:click="$dispatch('close')" class="c-btn-ghost">Cancelar</button><button class="c-btn-success">Confirmar</button></div>
                                        </form>
                                    </x-modal>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="fa-money-bill-transfer" title="Nenhum lançamento neste filtro" text="Lançamentos de orçamentos aprovados aparecem aqui automaticamente." /></td></tr>
                    @endforelse
                </tbody>
                @if($entries->isNotEmpty())
                    <tfoot class="bg-surface-50 dark:bg-surface-800/50 text-sm">
                        <tr>
                            <td colspan="6" class="c-td text-right text-surface-500">Nesta página: a receber <strong class="text-accent-700 dark:text-accent-400">@money($entries->where('direction', 'receivable')->where('status', '!=', 'canceled')->sum('amount'))</strong> · a pagar <strong class="text-red-600">@money($entries->where('direction', 'payable')->where('status', '!=', 'canceled')->sum('amount'))</strong></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $entries->links() }}</div>

    <x-modal name="new-entry" :show="$errors->hasAny(['description', 'amount', 'due_date', 'direction'])" maxWidth="lg">
        <form action="{{ route('finance.store') }}" method="POST" class="p-6 space-y-4" x-data="{ dir: @js(old('direction', $direction ?? 'payable')), n: {{ (int) old('installments', 1) }} }">
            @csrf
            <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Novo lançamento</h2>
            <div class="grid grid-cols-2 gap-2">
                @foreach(['receivable' => ['A receber', 'fa-arrow-down', 'peer-checked:border-accent-500 peer-checked:bg-accent-50 dark:peer-checked:bg-accent-950/30'], 'payable' => ['A pagar', 'fa-arrow-up', 'peer-checked:border-red-400 peer-checked:bg-red-50 dark:peer-checked:bg-red-950/30']] as $k => [$label, $icon, $cls])
                    <label class="cursor-pointer"><input type="radio" name="direction" value="{{ $k }}" x-model="dir" class="sr-only peer">
                        <span class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-surface-200 dark:border-surface-700 text-sm font-medium {{ $cls }}"><i class="fa-solid {{ $icon }}"></i>{{ $label }}</span></label>
                @endforeach
            </div>
            <div><label class="c-label">Descrição <span class="text-red-500">*</span></label><input name="description" required maxlength="255" value="{{ old('description') }}" class="c-input" placeholder="Ex.: Aluguel do escritório">@error('description')<p class="c-error">{{ $message }}</p>@enderror</div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div><label class="c-label">Valor total <span class="text-red-500">*</span></label><input type="number" step="0.01" min="0.01" name="amount" required value="{{ old('amount') }}" class="c-input">@error('amount')<p class="c-error">{{ $message }}</p>@enderror</div>
                <div><label class="c-label" x-text="n > 1 ? '1º vencimento' : 'Vencimento'"></label><input type="date" name="due_date" required value="{{ old('due_date', now()->format('Y-m-d')) }}" class="c-input"></div>
                <div><label class="c-label">Parcelas</label><input type="number" name="installments" min="1" max="60" x-model.number="n" class="c-input"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div><label class="c-label">Categoria</label>
                    <select name="category_id" class="c-input">
                        <option value="">—</option>
                        @foreach($categories as $c)<option value="{{ $c->id }}" x-show="dir === '{{ $c->type === 'income' ? 'receivable' : 'payable' }}'" @selected(old('category_id') === $c->id)>{{ $c->name }}</option>@endforeach
                    </select></div>
                <div x-show="dir === 'receivable'"><label class="c-label">Cliente</label>
                    <select name="client_id" class="c-input" :disabled="dir !== 'receivable'"><option value="">—</option>@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                <div x-show="dir === 'payable'"><label class="c-label">Fornecedor / favorecido</label><input name="counterparty" maxlength="255" class="c-input" :disabled="dir !== 'payable'"></div>
            </div>
            <div><label class="c-label">Forma de pagamento</label><select name="payment_method" class="c-input"><option value="">—</option>@foreach($paymentMethods as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach</select></div>
            <div class="flex justify-end gap-2 pt-2"><button type="button" x-on:click="$dispatch('close')" class="c-btn-ghost">Cancelar</button><button class="c-btn-primary">Lançar</button></div>
        </form>
    </x-modal>
</x-app-layout>
