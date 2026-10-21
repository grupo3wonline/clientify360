<x-app-layout>
    @php $editing = $form->exists; @endphp
    @section('title', $editing ? 'Editar formulário' : 'Novo formulário')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Captação de leads', 'url' => route('lead-forms.index')], ['label' => $editing ? $form->name : 'Novo']]" />
    </x-slot>

    <x-page-title :title="$editing ? 'Editar formulário' : 'Novo formulário de captação'" icon="fa-magnet" />

    <form action="{{ $editing ? route('lead-forms.update', $form) : route('lead-forms.store') }}" method="POST"
          x-data="leadFormBuilder(@js(array_values(old('fields', $form->fields ?? []))), @js(old('color', $form->color)), @js(old('title', $form->title)), @js(old('button_text', $form->button_text)))"
          class="grid grid-cols-1 xl:grid-cols-5 gap-6">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="xl:col-span-3 space-y-6">
            <div class="c-card p-5 space-y-4">
                <h3 class="text-sm font-semibold"><i class="fa-solid fa-sliders text-brand-500 mr-2"></i>Configuração</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="c-label">Nome interno <span class="text-red-500">*</span></label><input name="name" required maxlength="100" value="{{ old('name', $form->name) }}" class="c-input" placeholder="Ex.: Bio do Instagram" autocomplete="off">@error('name')<p class="c-error">{{ $message }}</p>@enderror</div>
                    <div><label class="c-label">Origem registrada no cliente</label>
                        <select name="default_source" class="c-input" autocomplete="off">@foreach($sources as $k => $label)<option value="{{ $k }}" @selected(old('default_source', $form->default_source) === $k)>{{ $label }}</option>@endforeach</select></div>
                </div>
                <div><label class="c-label">Título público <span class="text-red-500">*</span></label><input name="title" x-model="title" required maxlength="200" class="c-input" autocomplete="off">@error('title')<p class="c-error">{{ $message }}</p>@enderror</div>
                <div><label class="c-label">Texto de apoio</label><textarea name="description" rows="2" maxlength="1000" class="c-input resize-none" placeholder="Ex.: Receba um diagnóstico gratuito do seu site em 24h." autocomplete="off">{{ old('description', $form->description) }}</textarea></div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div><label class="c-label">Texto do botão</label><input name="button_text" x-model="button" required maxlength="50" class="c-input" autocomplete="off"></div>
                    <div><label class="c-label">Cor</label><input type="color" name="color" x-model="color" class="h-10 w-full rounded-lg border border-surface-200 dark:border-surface-700" autocomplete="off"></div>
                </div>
                <div><label class="c-label">Mensagem de sucesso</label><input name="success_message" maxlength="500" value="{{ old('success_message', $form->success_message) }}" class="c-input" autocomplete="off"></div>
                <div><label class="c-label">Redirecionar após envio (opcional)</label><input type="url" name="redirect_url" maxlength="500" value="{{ old('redirect_url', $form->redirect_url) }}" class="c-input" placeholder="https://seusite.com.br/obrigado" autocomplete="off">@error('redirect_url')<p class="c-error">{{ $message }}</p>@enderror</div>
            </div>

            <div class="c-card p-5 space-y-4">
                <h3 class="text-sm font-semibold"><i class="fa-solid fa-diagram-project text-brand-500 mr-2"></i>Automação</h3>
                <label class="flex items-start gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="create_deal" value="1" @checked(old('create_deal', $form->create_deal)) class="mt-0.5 rounded border-surface-300 text-brand-600" autocomplete="off">
                    <span>Criar oportunidade no funil para cada lead<span class="block text-xs text-surface-500">Leads com e-mail já cadastrado são vinculados ao cliente existente (sem duplicar).</span></span>
                </label>
                <div><label class="c-label">Etapa inicial</label>
                    <select name="pipeline_stage_id" class="c-input" autocomplete="off"><option value="">Primeira etapa do funil</option>@foreach($stages as $s)<option value="{{ $s->id }}" @selected(old('pipeline_stage_id', $form->pipeline_stage_id) === $s->id)>{{ $s->name }}</option>@endforeach</select></div>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $form->is_active)) class="rounded border-surface-300 text-brand-600" autocomplete="off"> Formulário ativo (aceitando envios)
                </label>
            </div>

            <div class="c-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold"><i class="fa-solid fa-list-ul text-brand-500 mr-2"></i>Campos</h3>
                    <div class="flex flex-wrap gap-1">
                        @foreach(\App\Models\LeadForm::MAPPABLE_FIELDS as $key => $label)
                            <button type="button" x-show="!has('{{ $key }}')" @click="add('{{ $key }}', @js($label), '{{ $key === 'email' ? 'email' : ($key === 'phone' ? 'tel' : ($key === 'message' ? 'textarea' : 'text')) }}')" class="text-[11px] px-2 py-1 rounded-lg bg-surface-100 dark:bg-surface-800 hover:bg-brand-50 dark:hover:bg-brand-950/40">+ {{ $label }}</button>
                        @endforeach
                        <button type="button" @click="add('', '', 'text')" class="text-[11px] px-2 py-1 rounded-lg bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300">+ Personalizado</button>
                    </div>
                </div>
                @error('fields') <p class="c-error mb-2">{{ $message }}</p> @enderror
                @if($errors->has('fields.*'))<p class="c-error mb-2">{{ collect($errors->get('fields.*'))->flatten()->first() }}</p>@endif
                <div class="space-y-2">
                    <template x-for="(f, i) in fields" :key="f.key">
                        <div class="grid grid-cols-12 gap-2 items-center p-2 rounded-xl bg-surface-50 dark:bg-surface-800/50">
                            <input :name="`fields[${i}][label]`" x-model="f.label" required maxlength="80" placeholder="Rótulo" class="c-input py-1.5 col-span-12 sm:col-span-4" autocomplete="off">
                            <input :name="`fields[${i}][name]`" x-model="f.name" required maxlength="40" placeholder="identificador" pattern="[a-z][a-z0-9_]*" title="Letras minúsculas, números e _" class="c-input py-1.5 font-mono text-xs col-span-5 sm:col-span-3" autocomplete="off">
                            <select :name="`fields[${i}][type]`" x-model="f.type" class="c-input py-1.5 col-span-4 sm:col-span-2" autocomplete="off">
                                @foreach(\App\Models\LeadForm::FIELD_TYPES as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach
                            </select>
                            <label class="col-span-2 sm:col-span-2 text-xs flex items-center gap-1 cursor-pointer"><input type="checkbox" :name="`fields[${i}][required]`" value="1" x-model="f.required" class="rounded border-surface-300 text-brand-600" autocomplete="off"> Obrig.</label>
                            <div class="col-span-1 flex justify-end gap-0.5">
                                <button type="button" @click="move(i, -1)" x-show="i > 0" class="text-surface-400 hover:text-surface-700 p-1" title="Subir"><i class="fa-solid fa-arrow-up text-xs"></i></button>
                                <button type="button" @click="fields.splice(i, 1)" class="text-surface-400 hover:text-red-600 p-1" title="Remover"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </template>
                </div>
                <p class="c-hint mt-3"><i class="fa-solid fa-circle-info mr-1"></i>Identificadores <code>name</code>, <code>email</code>, <code>phone</code>, <code>city</code>, <code>company</code> e <code>message</code> preenchem o cadastro do cliente. Os demais ficam nas observações.</p>
            </div>
        </div>

        {{-- Pré-visualização --}}
        <aside class="xl:col-span-2">
            <div class="xl:sticky xl:top-24 space-y-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-surface-400">Pré-visualização</p>
                <div class="c-card p-6 border-t-4" :style="`border-top-color: ${color}`">
                    <h2 class="text-xl font-bold text-surface-900 dark:text-white" x-text="title"></h2>
                    <div class="space-y-3 mt-4">
                        <template x-for="f in fields" :key="'p' + f.key">
                            <div>
                                <span class="c-label text-xs"><span x-text="f.label || '(sem rótulo)'"></span><span x-show="f.required" class="text-red-500"> *</span></span>
                                <div class="c-input bg-surface-50 dark:bg-surface-800/50" :class="f.type === 'textarea' ? 'h-16' : 'h-9'"></div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-4 w-full py-2.5 rounded-xl text-center text-sm font-semibold text-white" :style="`background: ${color}`" x-text="button"></div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ $editing ? route('lead-forms.show', $form) : route('lead-forms.index') }}" class="c-btn-ghost flex-1">Cancelar</a>
                    <button class="c-btn-primary flex-1" :disabled="!fields.length"><i class="fa-solid fa-floppy-disk mr-2 text-xs"></i> Salvar</button>
                </div>
            </div>
        </aside>
    </form>

    @push('scripts')
    <script>
        function leadFormBuilder(initial, color, title, button) {
            let seq = 0;
            const mk = (f) => ({ key: ++seq, name: f.name ?? '', label: f.label ?? '', type: f.type ?? 'text', required: !!(f.required && f.required !== '0') });
            return {
                fields: initial.map(mk), color, title, button,
                has(name) { return this.fields.some((f) => f.name === name); },
                add(name, label, type) { this.fields.push(mk({ name, label, type, required: name === 'name' || name === 'email' })); },
                move(i, d) { const [f] = this.fields.splice(i, 1); this.fields.splice(i + d, 0, f); },
            };
        }
    </script>
    @endpush
</x-app-layout>
