<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\ServiceOrder;
use App\Services\PdfService;
use App\Services\SubscriptionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\Personas;

beforeEach(function (): void {
    $this->other = Personas::owner('professional');
    $this->foreignQuote = Quote::factory()->forClient(Client::factory()->create(['account_id' => $this->other->account_id]))->create();

    $this->pro = Personas::owner('professional');
    $client = Client::factory()->create(['account_id' => $this->pro->account_id, 'name' => 'Cliente PDF']);
    $this->quote = Quote::factory()->forClient($client)->create(['number' => 'ORC-2026-0042']);
    QuoteItem::create(['quote_id' => $this->quote->id, 'description' => 'Consultoria', 'quantity' => 2, 'unit_price' => 50, 'total' => 100, 'position' => 1]);
    $this->order = ServiceOrder::factory()->forClient($client)->create(['number' => 'OS-2026-0007']);
});

it('downloads the quote PDF on Profissional and logs it', function (): void {
    $response = $this->actingAs($this->pro)->get(route('quotes.pdf', $this->quote))->assertOk();

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toContain('attachment')
        ->and($response->headers->get('Content-Disposition'))->toContain('orcamento-ORC-2026-0042.pdf');
    expect(Activity::where('log_name', 'privacy')->where('event', 'pdf.generated')->exists())->toBeTrue();
});

it('downloads the service order PDF on Profissional', function (): void {
    $response = $this->actingAs($this->pro)->get(route('service-orders.pdf', $this->order))->assertOk();

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toContain('ordem-de-servico-OS-2026-0007.pdf');
});

it('renders the number, items and the footer in pt-BR', function (): void {
    $this->quote->load(['items.product', 'client', 'account']);
    $html = view('pdf.quote', ['quote' => $this->quote, 'logo' => null])->render();

    expect($html)->toContain('Orçamento nº ORC-2026-0042')
        ->toContain('Consultoria')
        ->toContain('Cliente PDF')
        ->toContain('Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL · Gerado em');

    $this->order->load(['client', 'quote', 'account']);
    expect(view('pdf.service-order', ['order' => $this->order, 'logo' => null])->render())
        ->toContain('Ordem de Serviço nº OS-2026-0007');
});

it('escapes user content in the PDF', function (): void {
    $this->quote->update(['title' => '<script>alert(1)</script>']);
    $this->quote->load(['items.product', 'client', 'account']);

    expect(view('pdf.quote', ['quote' => $this->quote, 'logo' => null])->render())
        ->not->toContain('<script>alert(1)</script>')
        ->toContain('&lt;script&gt;');
});

it('forbids PDFs on the Básico plan', function (): void {
    $basic = Personas::owner('basic');
    $quote = Quote::factory()->forClient(Client::factory()->create(['account_id' => $basic->account_id]))->create();

    $this->actingAs($basic)->get(route('quotes.pdf', $quote))->assertForbidden();
    $this->actingAs($basic)->get(route('quotes.show', $quote))->assertOk()->assertSee('Disponível no plano Profissional');
});

it('returns 404 for another account quote', function (): void {
    $this->actingAs($this->pro)->get(route('quotes.pdf', $this->foreignQuote))->assertNotFound();
});

it('falls back to a pt-BR flash when generation fails', function (): void {
    Pdf::shouldReceive('loadView')->andThrow(new RuntimeException('dompdf boom'));

    $this->actingAs($this->pro)->from(route('quotes.show', $this->quote))
        ->get(route('quotes.pdf', $this->quote))
        ->assertRedirect(route('quotes.show', $this->quote))
        ->assertSessionHas('error', PdfService::FAILURE_MESSAGE);
});

it('keeps the Básico feature message for PDFs', function (): void {
    expect(app(SubscriptionService::class)->featureMessage('pdf'))->not->toBe('');
});
