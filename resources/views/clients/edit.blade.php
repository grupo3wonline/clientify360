<x-app-layout>
    @section('title', 'Editar Cliente')

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <a href="{{ route('clients.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Clientes</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Editar</span>
        </nav>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Editar Cliente</h1>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                <i class="fa-solid fa-circle-info text-brand-500 mr-1" aria-hidden="true"></i>
                Atualize os dados abaixo. Campos com <span class="text-danger-500 font-medium">*</span> são obrigatórios.
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
            <form action="{{ route('clients.update', $client) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-6 md:p-8 space-y-6">
                    <!-- Personal Info Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                            <i class="fa-solid fa-user text-brand-500 mr-2" aria-hidden="true"></i> Dados Pessoais
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                                    Nome <span class="text-danger-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name', $client->name) }}" autocomplete="off" required
                                       placeholder="Nome completo do cliente"
                                       @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('name')
                                    <p id="name-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">E-mail</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $client->email) }}" autocomplete="off"
                                       placeholder="email@exemplo.com"
                                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('email')
                                    <p id="email-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Telefone</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $client->phone) }}" autocomplete="off"
                                       placeholder="(00) 00000-0000"
                                       @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('phone')
                                    <p id="phone-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Person Type + Document -->
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5"
                                 x-data="documentField('{{ old('person_type', $client->person_type ?? 'individual') }}', '{{ old('document', $client->document) }}')">
                                <!-- Person Type -->
                                <div>
                                    <label for="person_type" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                                        Tipo de Pessoa <span class="text-danger-500">*</span>
                                    </label>
                                    <select name="person_type" id="person_type" x-model="personType" autocomplete="off" required
                                            @error('person_type') aria-invalid="true" aria-describedby="person_type-error" @enderror
                                            class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                        @foreach(\App\Models\Client::PERSON_TYPES as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('person_type')
                                        <p id="person_type-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Document -->
                                <div>
                                    <label for="document" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5"
                                           x-text="label" aria-live="polite">Documento</label>
                                    <input type="text" name="document" id="document" x-model="document" @input="format()"
                                           :placeholder="placeholder" :maxlength="maxLength" :aria-label="label"
                                           autocomplete="off" inputmode="numeric"
                                           @error('document') aria-invalid="true" aria-describedby="document-error" @enderror
                                           class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                    @error('document')
                                        <p id="document-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-surface-200 dark:border-surface-800">

                    <!-- Address Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                            <i class="fa-solid fa-location-dot text-brand-500 mr-2" aria-hidden="true"></i> Endereço
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Address -->
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Endereço</label>
                                <input type="text" name="address" id="address" value="{{ old('address', $client->address) }}" autocomplete="off"
                                       placeholder="Rua, número, complemento"
                                       @error('address') aria-invalid="true" aria-describedby="address-error" @enderror
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('address')
                                    <p id="address-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- City -->
                            <div>
                                <label for="city" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Cidade</label>
                                <input type="text" name="city" id="city" value="{{ old('city', $client->city) }}" autocomplete="off"
                                       placeholder="Nome da cidade"
                                       @error('city') aria-invalid="true" aria-describedby="city-error" @enderror
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('city')
                                    <p id="city-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- State -->
                            <div>
                                <label for="state" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Estado</label>
                                <select name="state" id="state" autocomplete="off"
                                        @error('state') aria-invalid="true" aria-describedby="state-error" @enderror
                                        class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                    <option value="">Selecione o estado</option>
                                    @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                        <option value="{{ $uf }}" {{ old('state', $client->state) === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <p id="state-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Zip Code -->
                            <div>
                                <label for="zip_code" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">CEP</label>
                                <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code', $client->zip_code) }}" autocomplete="off"
                                       placeholder="00000-000"
                                       @error('zip_code') aria-invalid="true" aria-describedby="zip_code-error" @enderror
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('zip_code')
                                    <p id="zip_code-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-surface-200 dark:border-surface-800">

                    <!-- Score Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                            <i class="fa-solid fa-star text-brand-500 mr-2" aria-hidden="true"></i> Pontuação / Score
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="score" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Score (0-100)</label>
                                <input type="number" name="score" id="score" value="{{ old('score', $client->score ?? 0) }}" min="0" max="100" autocomplete="off"
                                       @error('score') aria-invalid="true" aria-describedby="score-error" @enderror
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('score')
                                    <p id="score-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="score_label" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Classificação</label>
                                <select name="score_label" id="score_label" autocomplete="off"
                                        @error('score_label') aria-invalid="true" aria-describedby="score_label-error" @enderror
                                        class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                    <option value="">Selecione</option>
                                    <option value="Quente" {{ old('score_label', $client->score_label) === 'Quente' ? 'selected' : '' }}>🔥 Quente</option>
                                    <option value="Morno" {{ old('score_label', $client->score_label) === 'Morno' ? 'selected' : '' }}>🌤️ Morno</option>
                                    <option value="Frio" {{ old('score_label', $client->score_label) === 'Frio' ? 'selected' : '' }}>❄️ Frio</option>
                                    <option value="VIP" {{ old('score_label', $client->score_label) === 'VIP' ? 'selected' : '' }}>⭐ VIP</option>
                                    <option value="Novo" {{ old('score_label', $client->score_label) === 'Novo' ? 'selected' : '' }}>🆕 Novo</option>
                                </select>
                                @error('score_label')
                                    <p id="score_label-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-surface-200 dark:border-surface-800">

                    @include('clients._crm-fields', ['client' => $client])

                    <!-- Notes Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                            <i class="fa-solid fa-note-sticky text-brand-500 mr-2" aria-hidden="true"></i> Observações
                        </h3>
                        <textarea name="notes" id="notes" rows="4" autocomplete="off"
                                  placeholder="Informações adicionais sobre o cliente..."
                                  @error('notes') aria-invalid="true" aria-describedby="notes-error" @enderror
                                  class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition resize-none">{{ old('notes', $client->notes) }}</textarea>
                        @error('notes')
                            <p id="notes-error" class="mt-1.5 text-xs text-danger-600 dark:text-danger-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 md:px-8 py-4 bg-surface-50 dark:bg-surface-800/50 border-t border-surface-200 dark:border-surface-800 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                    <a href="{{ route('clients.index') }}"
                       class="inline-flex items-center justify-center px-5 py-2.5 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-700 dark:text-surface-300 text-sm font-medium rounded-xl transition">
                        <i class="fa-solid fa-xmark mr-2 text-xs" aria-hidden="true"></i> Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                        <i class="fa-solid fa-floppy-disk mr-2 text-xs" aria-hidden="true"></i> Atualizar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
