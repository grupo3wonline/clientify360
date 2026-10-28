<?php

declare(strict_types=1);

use App\Data\InvoiceData;
use App\Data\PlanData;
use App\Data\SubscriptionSummaryData;
use App\Exceptions\BillingUnavailableException;
use App\Services\SubscriptionService;
use App\Support\AccountCache;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Js;
use Mockery\MockInterface;
use Stripe\Exception\ApiConnectionException;
use Stripe\Invoice;
use Tests\Support\Personas;
use Tests\Support\Subscriptions;

/** Partial mock: Stripe-backed methods are stubbed, plan/usage logic stays real. */
function mockBilling(): MockInterface
{
    $mock = Mockery::mock(SubscriptionService::class, [new AccountCache])->makePartial();
    app()->instance(SubscriptionService::class, $mock);

    return $mock;
}

function activeSummary(array $overrides = []): SubscriptionSummaryData
{
    return new SubscriptionSummaryData(...array_merge([
        'plan' => PlanData::fromConfig('professional'),
        'status' => 'active',
        'statusLabel' => 'Ativa',
        'periodStart' => CarbonImmutable::parse('2026-03-05 10:00:00', 'America/Sao_Paulo'),
        'periodEnd' => CarbonImmutable::parse('2026-04-05 10:00:00', 'America/Sao_Paulo'),
        'cancelAtPeriodEnd' => false,
        'endsAt' => null,
    ], $overrides));
}

beforeEach(function (): void {
    Carbon::setTestNow('2026-03-20 12:00:00');
    $this->owner = Personas::owner();
    $this->actingAs($this->owner);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('shows the period dates and the countdown target of an active subscription (AC-23)', function (): void {
    mockBilling()->shouldReceive('summary')->andReturn(activeSummary());

    $this->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Minha assinatura')
        ->assertSee('Profissional')
        ->assertSee('Ativa')
        ->assertSee('Início do período')
        ->assertSee('05/03/2026')
        ->assertSee('Próxima renovação')
        ->assertSee('05/04/2026')
        ->assertSee('data-ends-at="2026-04-05T10:00:00-03:00"', false)
        ->assertSee('Renovação automática')
        ->assertSee('Voltar para o Básico ao fim do período');
});

it('shows the access end and the resume action while canceling', function (): void {
    mockBilling()->shouldReceive('summary')->andReturn(activeSummary([
        'status' => 'canceling',
        'statusLabel' => 'Cancelamento agendado',
        'cancelAtPeriodEnd' => true,
        'endsAt' => CarbonImmutable::parse('2026-04-05 10:00:00', 'America/Sao_Paulo'),
    ]));

    $this->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Cancelamento agendado')
        ->assertSee('Acesso até')
        ->assertSee('Fim do acesso Profissional')
        ->assertSee('Reativar assinatura');
});

it('offers the R$ 29,90 upgrade with the struck R$ 79,90 on Básico', function (): void {
    $this->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Gratuito')
        ->assertSee('Assinar Profissional por R$ 29,90/mês')
        ->assertSee('<s>R$ 79,90</s>', false)
        ->assertSee('62% OFF')
        ->assertDontSee('39,90')
        ->assertSee('data-loading-text="Redirecionando para o pagamento seguro..."', false);
});

it('explains when the period dates are unavailable', function (): void {
    mockBilling()->shouldReceive('summary')->andReturn(activeSummary([
        'periodStart' => null, 'periodEnd' => null, 'degraded' => true,
    ]));

    $this->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Não foi possível consultar as datas do período agora.');
});

it('lists invoices with BRL amount, status and Stripe links (AC-25)', function (): void {
    mockBilling()->shouldReceive('invoices')->andReturn(collect([
        new InvoiceData(
            id: 'in_1',
            date: CarbonImmutable::parse('2026-03-05', 'America/Sao_Paulo'),
            total: 'R$ 29,90',
            status: 'paid',
            statusLabel: 'Pago',
            pdfUrl: 'https://pay.stripe.com/invoice/in_1/pdf',
            receiptUrl: 'https://invoice.stripe.com/i/in_1',
        ),
    ]));

    $this->get(route('billing.invoices'))
        ->assertOk()
        ->assertSee('Histórico de pagamentos')
        ->assertSee('05/03/2026')
        ->assertSee('R$ 29,90')
        ->assertSee('Pago')
        ->assertSee('href="https://pay.stripe.com/invoice/in_1/pdf"', false)
        ->assertSee('href="https://invoice.stripe.com/i/in_1"', false)
        ->assertSee('rel="noopener"', false);
});

it('renders the error state when Stripe is unavailable (AC-25)', function (): void {
    mockBilling()->shouldReceive('invoices')->andThrow(new BillingUnavailableException('down'));

    $this->get(route('billing.invoices'))
        ->assertOk()
        ->assertSee('Não foi possível carregar o histórico agora. Tente novamente em instantes.');
});

it('renders the empty state without invoices', function (): void {
    $this->get(route('billing.invoices'))
        ->assertOk()
        ->assertSee('Nenhum pagamento registrado');
});

it('redirects the upgrade to Stripe Checkout (AC-24)', function (): void {
    config(['plans.professional.stripe_price_id' => 'price_test_2990']);
    mockBilling()->shouldReceive('createCheckoutSession')
        ->once()->with(Mockery::any(), 'price_test_2990')
        ->andReturn('https://checkout.stripe.com/c/pay/cs_test_1');

    $this->post(route('billing.subscribe'))->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_1');
});

it('does not start a second checkout for a Profissional account (AC-24)', function (): void {
    Subscriptions::makeProfessional($this->owner->account);
    $this->actingAs($this->owner->fresh());
    mockBilling()->shouldNotReceive('createCheckoutSession');

    $this->post(route('billing.subscribe'))
        ->assertRedirect(route('billing.index'))
        ->assertSessionHas('info', 'Sua conta já está no plano Profissional.');
});

it('reports a missing price id', function (): void {
    config(['plans.professional.stripe_price_id' => '']);

    $this->post(route('billing.subscribe'))
        ->assertSessionHas('error', 'Plano Profissional não configurado. Entre em contato com o suporte.');
});

it('reports Stripe checkout failures', function (): void {
    config(['plans.professional.stripe_price_id' => 'price_test_2990']);
    mockBilling()->shouldReceive('createCheckoutSession')->andThrow(new ApiConnectionException('timeout'));

    $this->post(route('billing.subscribe'))
        ->assertRedirect(route('billing.index'))
        ->assertSessionHas('error', 'Não foi possível iniciar o pagamento agora. Tente novamente em instantes.');
});

it('cancels at period end and resumes with pt-BR messages', function (): void {
    $mock = mockBilling();
    $mock->shouldReceive('cancelSubscription')->once()->andReturn(Carbon::parse('2026-04-05 13:00:00', 'UTC'));
    $mock->shouldReceive('resumeSubscription')->once();

    $this->post(route('billing.cancel'))
        ->assertRedirect(route('billing.index'))
        ->assertSessionHas('success', 'Assinatura cancelada. Você continua no Profissional até 05/04/2026; depois a conta volta ao Básico.');

    $this->post(route('billing.resume'))
        ->assertRedirect(route('billing.index'))
        ->assertSessionHas('success', 'Assinatura reativada com sucesso!');
});

it('handles the Checkout return flags', function (): void {
    mockBilling()->shouldReceive('syncSubscriptionFromStripe')->once();

    $this->get(route('billing.index', ['checkout' => 'success']))
        ->assertOk()
        ->assertSee(Js::from('Estamos confirmando seu pagamento. Isso pode levar alguns instantes.')->toHtml(), false);

    $this->get(route('billing.index', ['checkout' => 'cancel']))
        ->assertOk()
        ->assertSee(Js::from('Pagamento não concluído. Nenhuma cobrança foi feita.')->toHtml(), false);
});

it('welcomes the account to Profissional when the Checkout sync confirms it', function (): void {
    $mock = mockBilling();
    $mock->shouldReceive('syncSubscriptionFromStripe')->once()
        ->andReturnUsing(fn () => Subscriptions::makeProfessional($this->owner->account));
    $mock->shouldReceive('summary')->andReturn(activeSummary());

    $this->get(route('billing.index', ['checkout' => 'success']))
        ->assertOk()
        ->assertSee(Js::from('Pagamento confirmado! Bem-vindo ao plano Profissional.')->toHtml(), false);
});

it('keeps both billing pages owner-only (account.billing)', function (): void {
    $manager = Personas::manager($this->owner->account);
    $collaborator = Personas::collaborator($this->owner->account);

    foreach ([$manager, $collaborator] as $member) {
        $this->actingAs($member);
        $this->get(route('billing.index'))->assertForbidden();
        $this->get(route('billing.invoices'))->assertForbidden();
        $this->post(route('billing.cancel'))->assertForbidden();
    }
});

it('normalizes Cashier invoices to the app money format', function (): void {
    $account = $this->owner->account;
    $account->forceFill(['stripe_id' => 'cus_invoice_test'])->save();

    $invoice = new Laravel\Cashier\Invoice($account, Invoice::constructFrom([
        'id' => 'in_42',
        'customer' => 'cus_invoice_test',
        'created' => 1772712000,
        'currency' => 'brl',
        'total' => 2990,
        'starting_balance' => 0,
        'status' => 'paid',
        'invoice_pdf' => 'https://pay.stripe.com/invoice/in_42/pdf',
        'hosted_invoice_url' => 'https://invoice.stripe.com/i/in_42',
    ]));

    $data = InvoiceData::fromCashier($invoice);

    expect($data->total)->toBe('R$ 29,90')
        ->and($data->statusLabel)->toBe('Pago')
        ->and($data->receiptUrl)->toBe('https://invoice.stripe.com/i/in_42')
        ->and(InvoiceData::fromCache($data->toCache())->total)->toBe('R$ 29,90');
});
