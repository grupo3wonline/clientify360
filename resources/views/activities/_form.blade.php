{{-- Registrar atividade. Recebe $client (opcional), $deal (opcional), $clients (quando sem cliente fixo). --}}
<form autocomplete="off" action="{{ route('activities.store') }}" method="POST" class="space-y-3" x-data="{ next: {{ old('next_step') ? 'true' : 'false' }} }">
    @csrf
    @if(!empty($client))
        <input autocomplete="off" type="hidden" name="client_id" value="{{ $client->id }}">
    @else
        <div>
            <label class="c-label" for="act-client">Cliente</label>
            <select autocomplete="off" id="act-client" name="client_id" required class="c-input">
                <option value="">Selecione...</option>
                @foreach($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id') === $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            @error('client_id') <p class="c-error">{{ $message }}</p> @enderror
        </div>
    @endif
    @if(!empty($deal))<input autocomplete="off" type="hidden" name="deal_id" value="{{ $deal->id }}">@endif

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="c-label" for="act-type">Tipo</label>
            <select autocomplete="off" id="act-type" name="type" class="c-input">
                @foreach(\App\Models\Activity::TYPES as $k => $t)<option value="{{ $k }}" @selected(old('type', 'call') === $k)>{{ $t['label'] }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="c-label" for="act-outcome">Resultado</label>
            <select autocomplete="off" id="act-outcome" name="outcome" class="c-input">
                <option value="">—</option>
                @foreach(\App\Models\Activity::OUTCOMES as $k => $label)<option value="{{ $k }}" @selected(old('outcome') === $k)>{{ $label }}</option>@endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="c-label" for="act-title">O que aconteceu? <span class="text-danger-500">*</span></label>
        <input autocomplete="off" id="act-title" name="title" required maxlength="255" value="{{ old('title') }}" class="c-input" placeholder="Ex.: Ligação de apresentação">
        @error('title') <p class="c-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="c-label" for="act-desc">Detalhes</label>
        <textarea autocomplete="off" id="act-desc" name="description" rows="2" class="c-input resize-none">{{ old('description') }}</textarea>
    </div>
    <label class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-300 cursor-pointer">
        <input autocomplete="off" type="checkbox" x-model="next" class="rounded border-surface-300 text-brand-600 focus:ring-brand-500"> Agendar próximo passo
    </label>
    <div x-show="next" x-cloak class="space-y-3 p-3 rounded-xl bg-brand-50/60 dark:bg-brand-950/30">
        <div>
            <label class="c-label" for="act-next">Próximo passo</label>
            <input autocomplete="off" id="act-next" name="next_step" maxlength="1000" value="{{ old('next_step') }}" class="c-input" placeholder="Ex.: Enviar proposta revisada" :disabled="!next">
            @error('next_step') <p class="c-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="c-label" for="act-next-at">Quando?</label>
            <input autocomplete="off" id="act-next-at" type="datetime-local" name="next_step_at" value="{{ old('next_step_at') }}" class="c-input" :disabled="!next">
            @error('next_step_at') <p class="c-error">{{ $message }}</p> @enderror
            <p class="c-hint"><i class="fa-solid fa-bell mr-1"></i>Vira um lembrete na Agenda e aparece nas Notificações.</p>
        </div>
    </div>
    <button class="c-btn-primary w-full"><i class="fa-solid fa-plus mr-2 text-xs"></i> Registrar</button>
</form>
