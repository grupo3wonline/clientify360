@if($invoice->pdfUrl)
    <a href="{{ $invoice->pdfUrl }}" target="_blank" rel="noopener" class="inline-flex items-center font-medium text-brand-600 dark:text-brand-400 hover:underline">
        <i class="fa-solid fa-file-pdf mr-1"></i> Baixar PDF
    </a>
@endif
@if($invoice->receiptUrl)
    <a href="{{ $invoice->receiptUrl }}" target="_blank" rel="noopener" class="inline-flex items-center font-medium text-brand-600 dark:text-brand-400 hover:underline">
        <i class="fa-solid fa-receipt mr-1"></i> Ver recibo
    </a>
@endif
@if(! $invoice->pdfUrl && ! $invoice->receiptUrl)
    <span class="text-surface-400">—</span>
@endif
