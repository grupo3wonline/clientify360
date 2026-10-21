@php
    /** @var \App\Models\ServiceOrder|null $order */
    $order = $order ?? null;
    $isEdit = $order !== null;
    $fieldClass = 'w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition';
    $labelClass = 'block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1';
    $fmt = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d\TH:i') : '';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label for="title" class="{{ $labelClass }}">Título <span class="text-danger-500">*</span></label>
        <input type="text" name="title" id="title" required autocomplete="off"
               value="{{ old('title', $order?->title) }}"
               placeholder="Ex: Instalação e configuração do equipamento"
               class="{{ $fieldClass }}">
        @error('title')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="client_id" class="{{ $labelClass }}">Cliente <span class="text-danger-500">*</span></label>
        <select name="client_id" id="client_id" required autocomplete="off" class="{{ $fieldClass }}">
            <option value="">Selecione o cliente</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id', $order?->client_id) === $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
            @endforeach
        </select>
        @error('client_id')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="quote_id" class="{{ $labelClass }}">Orçamento vinculado (opcional)</label>
        <select name="quote_id" id="quote_id" autocomplete="off" class="{{ $fieldClass }}">
            <option value="">Nenhum orçamento</option>
            @foreach($quotes as $quote)
                <option value="{{ $quote->id }}" {{ old('quote_id', $order?->quote_id) === $quote->id ? 'selected' : '' }}>{{ $quote->number }} — {{ $quote->title }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-surface-400">Ao vincular um orçamento aprovado, a cobrança não é gerada novamente (evita cobrar em dobro).</p>
        @error('quote_id')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="total" class="{{ $labelClass }}">Valor total (R$) <span class="text-danger-500">*</span></label>
        <input type="number" name="total" id="total" step="0.01" min="0" required autocomplete="off"
               value="{{ old('total', $order?->total ?? '0.00') }}"
               class="{{ $fieldClass }}">
        @error('total')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="opened_at" class="{{ $labelClass }}">Abertura <span class="text-danger-500">*</span></label>
        <input type="datetime-local" name="opened_at" id="opened_at" required autocomplete="off"
               value="{{ old('opened_at', $fmt($order?->opened_at ?? now())) }}"
               class="{{ $fieldClass }}">
        @error('opened_at')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="expected_at" class="{{ $labelClass }}">Previsão de entrega</label>
        <input type="datetime-local" name="expected_at" id="expected_at" autocomplete="off"
               value="{{ old('expected_at', $fmt($order?->expected_at)) }}"
               class="{{ $fieldClass }}">
        @error('expected_at')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    @if($isEdit)
        <div>
            <label for="status" class="{{ $labelClass }}">Situação <span class="text-danger-500">*</span></label>
            <select name="status" id="status" autocomplete="off" class="{{ $fieldClass }}">
                @foreach(\App\Models\ServiceOrder::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $order?->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="completed_at" class="{{ $labelClass }}">Conclusão</label>
            <input type="datetime-local" name="completed_at" id="completed_at" autocomplete="off"
                   value="{{ old('completed_at', $fmt($order?->completed_at)) }}"
                   class="{{ $fieldClass }}">
            @error('completed_at')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
        </div>
    @endif

    <div class="md:col-span-2">
        <label for="description" class="{{ $labelClass }}">Descrição</label>
        <textarea name="description" id="description" rows="3" autocomplete="off"
                  placeholder="Detalhes do serviço a executar..."
                  class="{{ $fieldClass }} resize-none">{{ old('description', $order?->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>
</div>
