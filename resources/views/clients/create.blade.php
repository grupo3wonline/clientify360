<x-app-layout>
    @section('title', 'Novo Cliente')

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <a href="{{ route('clients.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Clientes</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Novo</span>
        </nav>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Novo Cliente</h1>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                <i class="fa-solid fa-circle-info text-brand-500 mr-1"></i>
                Preencha os dados abaixo. Campos com <span class="text-red-500 font-medium">*</span> são obrigatórios.
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
            <form action="{{ route('clients.store') }}" method="POST">
                @csrf

                <div class="p-6 md:p-8 space-y-6">
                    <!-- Personal Info Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                            <i class="fa-solid fa-user text-brand-500 mr-2"></i> Dados Pessoais
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                                    Nome <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" autocomplete="off" required
                                       placeholder="Nome completo do cliente"
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('name')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">E-mail</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="off"
                                       placeholder="email@exemplo.com"
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('email')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Telefone</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" autocomplete="off"
                                       placeholder="(00) 00000-0000"
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('phone')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Document -->
                            <div>
                                <label for="document" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Documento</label>
                                <input type="text" name="document" id="document" value="{{ old('document') }}" autocomplete="off"
                                       placeholder="CPF ou CNPJ"
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('document')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-surface-200 dark:border-surface-800">

                    <!-- Address Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                            <i class="fa-solid fa-location-dot text-brand-500 mr-2"></i> Endereço
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Address -->
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Endereço</label>
                                <input type="text" name="address" id="address" value="{{ old('address') }}" autocomplete="off"
                                       placeholder="Rua, número, complemento"
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('address')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- City -->
                            <div>
                                <label for="city" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Cidade</label>
                                <input type="text" name="city" id="city" value="{{ old('city') }}" autocomplete="off"
                                       placeholder="Nome da cidade"
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('city')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- State -->
                            <div>
                                <label for="state" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Estado</label>
                                <select name="state" id="state" autocomplete="off"
                                        class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                    <option value="">Selecione o estado</option>
                                    @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                        <option value="{{ $uf }}" {{ old('state') === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Zip Code -->
                            <div>
                                <label for="zip_code" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">CEP</label>
                                <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}" autocomplete="off"
                                       placeholder="00000-000"
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('zip_code')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-surface-200 dark:border-surface-800">

                    <!-- Score Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                            <i class="fa-solid fa-star text-brand-500 mr-2"></i> Pontuação / Score
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="score" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Score (0-100)</label>
                                <input type="number" name="score" id="score" value="{{ old('score', 0) }}" min="0" max="100" autocomplete="off"
                                       class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                @error('score')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="score_label" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Classificação</label>
                                <select name="score_label" id="score_label" autocomplete="off"
                                        class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                    <option value="">Selecione</option>
                                    <option value="Quente" {{ old('score_label') === 'Quente' ? 'selected' : '' }}>🔥 Quente</option>
                                    <option value="Morno" {{ old('score_label') === 'Morno' ? 'selected' : '' }}>🌤️ Morno</option>
                                    <option value="Frio" {{ old('score_label') === 'Frio' ? 'selected' : '' }}>❄️ Frio</option>
                                    <option value="VIP" {{ old('score_label') === 'VIP' ? 'selected' : '' }}>⭐ VIP</option>
                                    <option value="Novo" {{ old('score_label') === 'Novo' ? 'selected' : '' }}>🆕 Novo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-surface-200 dark:border-surface-800">

                    <!-- Notes Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                            <i class="fa-solid fa-note-sticky text-brand-500 mr-2"></i> Observações
                        </h3>
                        <textarea name="notes" id="notes" rows="4" autocomplete="off"
                                  placeholder="Informações adicionais sobre o cliente..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition resize-none">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 md:px-8 py-4 bg-surface-50 dark:bg-surface-800/50 border-t border-surface-200 dark:border-surface-800 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                    <a href="{{ route('clients.index') }}"
                       class="inline-flex items-center justify-center px-5 py-2.5 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-700 dark:text-surface-300 text-sm font-medium rounded-xl transition">
                        <i class="fa-solid fa-xmark mr-2 text-xs"></i> Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                        <i class="fa-solid fa-floppy-disk mr-2 text-xs"></i> Salvar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
