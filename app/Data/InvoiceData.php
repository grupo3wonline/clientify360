<?php

declare(strict_types=1);

namespace App\Data;

use App\Support\Format;
use Carbon\CarbonImmutable;
use Laravel\Cashier\Invoice;
use Spatie\LaravelData\Data;

/** A Stripe invoice as listed on "Histórico de pagamentos". */
final class InvoiceData extends Data
{
    public const STATUS_LABELS = [
        'paid' => 'Pago',
        'open' => 'Em aberto',
        'void' => 'Anulada',
        'uncollectible' => 'Não cobrável',
        'draft' => 'Rascunho',
    ];

    public function __construct(
        public readonly string $id,
        public readonly CarbonImmutable $date,
        public readonly string $total,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly ?string $pdfUrl,
        public readonly ?string $receiptUrl,
    ) {}

    /** @return array<string, mixed> Scalars only (the cache does not unserialize objects). */
    public function toCache(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->getTimestamp(),
            'total' => $this->total,
            'status' => $this->status,
            'pdf_url' => $this->pdfUrl,
            'receipt_url' => $this->receiptUrl,
        ];
    }

    /** @param  array<string, mixed>  $row */
    public static function fromCache(array $row): self
    {
        $status = (string) $row['status'];

        return new self(
            id: (string) $row['id'],
            date: CarbonImmutable::createFromTimestamp((int) $row['date'], config('app.timezone')),
            total: (string) $row['total'],
            status: $status,
            statusLabel: self::STATUS_LABELS[$status] ?? ucfirst($status),
            pdfUrl: $row['pdf_url'] ?? null,
            receiptUrl: $row['receipt_url'] ?? null,
        );
    }

    public static function fromCashier(Invoice $invoice): self
    {
        $stripe = $invoice->asStripeInvoice();
        $status = (string) ($stripe->status ?? 'open');

        // Cashier's intl formatter emits "R$" + U+00A0; normalize to the app format.
        $total = extension_loaded('intl')
            ? str_replace("\u{00A0}", ' ', $invoice->total())
            : Format::money($invoice->rawTotal() / 100);

        return new self(
            id: (string) $stripe->id,
            date: CarbonImmutable::instance($invoice->date(config('app.timezone'))),
            total: $total,
            status: $status,
            statusLabel: self::STATUS_LABELS[$status] ?? ucfirst($status),
            pdfUrl: $stripe->invoice_pdf ?? null,
            receiptUrl: $stripe->hosted_invoice_url ?? null,
        );
    }
}
