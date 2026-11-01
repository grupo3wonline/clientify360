{{-- Anexos do cliente (Media Library, disco privado). Variáveis: $client, $attachments, $attachmentLimit, $attachmentUsage --}}
<section id="anexos" class="mt-6 bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6" aria-labelledby="anexos-title">
    <h3 id="anexos-title" class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-1 flex items-center gap-2">
        <i class="fa-solid fa-paperclip text-brand-500" aria-hidden="true"></i> Anexos
        <x-tooltip text="Guarde contratos, propostas assinadas, documentos e fotos do cliente. Os arquivos ficam em área privada: só usuários da sua conta conseguem baixar." />
    </h3>
    <p class="text-xs text-surface-500 dark:text-surface-400 mb-4">
        Formatos: PDF, imagens, Word, Excel ou texto. Máximo 10 MB por arquivo.
        Seu plano permite {{ $attachmentLimit < 0 ? 'anexos ilimitados' : $attachmentLimit.' anexos' }} ({{ $attachmentUsage }} usados).
    </p>

    @if($attachments->isEmpty())
        <p class="text-sm text-surface-400 italic mb-4">Nenhum anexo ainda.</p>
    @else
        <ul class="divide-y divide-surface-200 dark:divide-surface-800 mb-4">
            @foreach($attachments as $media)
                <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-surface-800 dark:text-surface-200 truncate">{{ $media->name }}</p>
                        <p class="text-xs text-surface-500 dark:text-surface-400">
                            {{ $media->human_readable_size }} · {{ $media->created_at?->format('d/m/Y H:i') }}
                            @if($media->getCustomProperty('uploaded_by_name'))
                                · por {{ $media->getCustomProperty('uploaded_by_name') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('clients.attachments.show', [$client, $media->id]) }}" data-no-loading
                           class="c-btn-ghost text-xs" title="Baixar {{ $media->name }}">
                            <i class="fa-solid fa-download mr-1" aria-hidden="true"></i> Baixar
                        </a>
                        @can('update', $client)
                            <x-confirm-delete :action="route('clients.attachments.destroy', [$client, $media->id])"
                                              title="Excluir este anexo?" text="O arquivo será apagado definitivamente." label="Excluir anexo" />
                        @endcan
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @can('update', $client)
        <form method="POST" action="{{ route('clients.attachments.store', $client) }}" enctype="multipart/form-data" autocomplete="off"
              class="flex flex-col sm:flex-row sm:items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="attachment-file" class="block text-xs font-medium text-surface-600 dark:text-surface-400 mb-1">Novo anexo</label>
                <input id="attachment-file" type="file" name="file" required autocomplete="off"
                       accept=".pdf,.jpg,.jpeg,.png,.webp,.txt,.csv,.doc,.docx,.xls,.xlsx"
                       class="block w-full text-sm text-surface-700 dark:text-surface-300 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700">
                @error('file')
                    <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="c-btn-primary" data-loading-text="Enviando arquivo...">
                <i class="fa-solid fa-upload mr-2" aria-hidden="true"></i> Enviar
            </button>
        </form>
    @endcan
</section>
