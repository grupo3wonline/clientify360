{{-- Campos compartilhados de oportunidade. $deal pode ser null (criação). $clients e $stages opcionais. --}}
@php
    $clients ??= \App\Models\Client::orderBy('name')->get(['id', 'name']);
    $stagesList = $stages ?? \App\Models\PipelineStage::ordered()->get();
@endphp
<div>
    <label class="c-label" for="deal-title">Título <span class="text-danger-500">*</span></label>
    <input autocomplete="off" id="deal-title" name="title" value="{{ old('title', $deal?->title) }}" required maxlength="255" class="c-input" placeholder="Ex.: Site institucional + SEO">
    @error('title') <p class="c-error">{{ $message }}</p> @enderror
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="c-label" for="deal-client">Cliente <span class="text-danger-500">*</span></label>
        @if(!empty($fixedClient))
            <input autocomplete="off" type="hidden" name="client_id" value="{{ $fixedClient->id }}">
            <input autocomplete="off" type="hidden" name="redirect_to" value="client">
            <p class="c-input bg-surface-50 dark:bg-surface-800/60">{{ $fixedClient->name }}</p>
        @else
            <select autocomplete="off" id="deal-client" name="client_id" required class="c-input">
                <option value="">Selecione...</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" @selected(old('client_id', $deal?->client_id) === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        @endif
        @error('client_id') <p class="c-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="c-label" for="deal-value">Valor estimado (R$) <span class="text-danger-500">*</span></label>
        <input autocomplete="off" id="deal-value" type="number" step="0.01" min="0" name="value" value="{{ old('value', $deal?->value ?? 0) }}" required class="c-input">
        @error('value') <p class="c-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="c-label" for="deal-stage">Etapa</label>
        <select autocomplete="off" id="deal-stage" name="pipeline_stage_id" class="c-input">
            @foreach($stagesList as $s)
                <option value="{{ $s->id }}" @selected(old('pipeline_stage_id', $deal?->pipeline_stage_id) === $s->id)>{{ $s->name }} ({{ $s->probability }}%)</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="c-label" for="deal-date">Previsão de fechamento</label>
        <input autocomplete="off" id="deal-date" type="date" name="expected_close_date" value="{{ old('expected_close_date', $deal?->expected_close_date?->format('Y-m-d')) }}" class="c-input">
    </div>
    <div class="sm:col-span-2">
        <label class="c-label" for="deal-source">Origem</label>
        <select autocomplete="off" id="deal-source" name="source" class="c-input">
            <option value="">—</option>
            @foreach(\App\Models\Client::SOURCES as $k => $label)
                <option value="{{ $k }}" @selected(old('source', $deal?->source) === $k)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div>
    <label class="c-label" for="deal-desc">Descrição</label>
    <textarea autocomplete="off" id="deal-desc" name="description" rows="3" maxlength="2000" class="c-input resize-none" placeholder="Necessidade do cliente, escopo, concorrentes...">{{ old('description', $deal?->description) }}</textarea>
</div>
