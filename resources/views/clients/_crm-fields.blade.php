{{-- Origem do lead + tags (CRM). Usado em clients/create e clients/edit. --}}
@php
    $selectedSource = old('source', $client?->source);
    $selectedTags = collect(old('tag_ids', $client?->tags?->pluck('id')->all() ?? []));
@endphp
<div>
    <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
        <i class="fa-solid fa-bullseye text-brand-500 mr-2"></i> Relacionamento comercial
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="source" class="c-label">Como chegou até você?</label>
            <select name="source" id="source" class="c-input">
                <option value="">Não informado</option>
                @foreach(\App\Models\Client::SOURCES as $value => $label)
                    <option value="{{ $value }}" @selected($selectedSource === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="c-hint">Alimenta o relatório de origem dos clientes.</p>
            @error('source') <p class="c-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <span class="c-label">Tags</span>
            <input type="hidden" name="_tags" value="1">
            @if($tags->isEmpty())
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-2">
                    Nenhuma tag criada ainda. <a href="{{ route('tags.index') }}" class="text-brand-600 dark:text-brand-400 hover:underline" target="_blank">Criar tags</a>
                </p>
            @else
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($tags as $tag)
                        <label x-data="{ on: {{ $selectedTags->contains($tag->id) ? 'true' : 'false' }} }"
                               class="cursor-pointer inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium border transition focus-within:ring-2 focus-within:ring-brand-500"
                               :class="on ? 'text-white border-transparent' : 'border-surface-200 dark:border-surface-700 text-surface-600 dark:text-surface-300'"
                               :style="on ? 'background: {{ $tag->color }}' : ''">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" x-model="on" class="sr-only" @checked($selectedTags->contains($tag->id))>
                            <span class="w-2 h-2 rounded-full mr-1.5 ring-1 ring-white/60" style="background: {{ $tag->color }}"></span>{{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            @endif
            @error('tag_ids.*') <p class="c-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<hr class="border-surface-200 dark:border-surface-800">
