@php
    /** @var \App\Models\Appointment|null $appointment */
    $appointment = $appointment ?? null;
    $isEdit = $appointment !== null;
    $fieldClass = 'w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition';
    $labelClass = 'block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1';
    $fmt = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d\TH:i') : '';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label for="title" class="{{ $labelClass }}">Título <span class="text-danger-500">*</span></label>
        <input type="text" name="title" id="title" required autocomplete="off"
               value="{{ old('title', $appointment?->title) }}"
               placeholder="Ex: Reunião com cliente"
               class="{{ $fieldClass }}">
        @error('title')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="starts_at" class="{{ $labelClass }}">Início <span class="text-danger-500">*</span></label>
        <input type="datetime-local" name="starts_at" id="starts_at" required autocomplete="off"
               value="{{ old('starts_at', $fmt($appointment?->starts_at)) }}"
               class="{{ $fieldClass }}">
        @error('starts_at')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="ends_at" class="{{ $labelClass }}">Término</label>
        <input type="datetime-local" name="ends_at" id="ends_at" autocomplete="off"
               value="{{ old('ends_at', $fmt($appointment?->ends_at)) }}"
               class="{{ $fieldClass }}">
        @error('ends_at')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="type" class="{{ $labelClass }}">Tipo <span class="text-danger-500">*</span></label>
        <select name="type" id="type" autocomplete="off" class="{{ $fieldClass }}">
            @foreach(\App\Models\Appointment::TYPES as $value => $label)
                <option value="{{ $value }}" {{ old('type', $appointment?->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    @if($isEdit)
        <div>
            <label for="status" class="{{ $labelClass }}">Situação <span class="text-danger-500">*</span></label>
            <select name="status" id="status" autocomplete="off" class="{{ $fieldClass }}">
                @foreach(\App\Models\Appointment::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $appointment?->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
        </div>
    @endif

    <div>
        <label for="client_id" class="{{ $labelClass }}">Cliente (opcional)</label>
        <select name="client_id" id="client_id" autocomplete="off" class="{{ $fieldClass }}">
            <option value="">Nenhum cliente</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id', $appointment?->client_id) === $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
            @endforeach
        </select>
        @error('client_id')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="deal_id" class="{{ $labelClass }}">Oportunidade (opcional)</label>
        <select name="deal_id" id="deal_id" autocomplete="off" class="{{ $fieldClass }}">
            <option value="">Nenhuma oportunidade</option>
            @foreach($deals as $deal)
                <option value="{{ $deal->id }}" {{ old('deal_id', $appointment?->deal_id) === $deal->id ? 'selected' : '' }}>{{ $deal->title }}</option>
            @endforeach
        </select>
        @error('deal_id')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="location" class="{{ $labelClass }}">Local</label>
        <input type="text" name="location" id="location" autocomplete="off"
               value="{{ old('location', $appointment?->location) }}"
               placeholder="Ex: Escritório, link da videochamada..."
               class="{{ $fieldClass }}">
        @error('location')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="color" class="{{ $labelClass }}">Cor (opcional)</label>
        <input type="color" name="color" id="color" autocomplete="off"
               value="{{ old('color', $appointment?->color ?? '#6366f1') }}"
               class="w-full h-10 px-1 py-1 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 cursor-pointer">
        @error('color')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="{{ $labelClass }}">Descrição</label>
        <textarea name="description" id="description" rows="3" autocomplete="off"
                  placeholder="Detalhes do agendamento..."
                  class="{{ $fieldClass }} resize-none">{{ old('description', $appointment?->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
    </div>

    @unless($isEdit)
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-surface-700 dark:text-surface-300 cursor-pointer">
                <input autocomplete="off" type="hidden" name="generate_reminder" value="0">
                <input autocomplete="off" type="checkbox" name="generate_reminder" value="1" {{ old('generate_reminder') ? 'checked' : '' }}
                       class="rounded border-surface-300 dark:border-surface-600 text-brand-600 focus:ring-brand-500/30">
                Gerar lembrete na Agenda para a data de início
            </label>
        </div>
    @endunless
</div>
