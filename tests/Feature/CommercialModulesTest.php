<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Deal;
use App\Models\FinancialEntry;
use App\Models\LeadForm;
use App\Models\LeadFormSubmission;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Reminder;
use App\Models\Tag;
use App\Models\User;
use App\Services\FinanceService;
use App\Services\QuoteService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobre os módulos mesclados de bkp-after_features: CRM, Vendas/Estoque,
 * Financeiro e Captação de leads.
 */
class CommercialModulesTest extends TestCase
{
    use RefreshDatabase;

    private function clientFor(User $user, array $attrs = []): Client
    {
        $this->actingAs($user);

        return Client::create(['name' => 'Cliente Teste', 'email' => 'c'.uniqid().'@ex.com'] + $attrs);
    }

    // ---------------------------------------------------------------- telas

    public function test_all_new_screens_render_for_a_user_with_data(): void
    {
        $user = User::factory()->create();
        $client = $this->clientFor($user);
        $product = Product::create(['name' => 'Camiseta', 'price' => 50, 'cost' => 20, 'track_stock' => true, 'stock' => 3, 'min_stock' => 5]);
        PipelineStage::ensureDefaultsFor($user->account_id);
        $deal = Deal::create(['client_id' => $client->id, 'title' => 'Site', 'value' => 1000, 'pipeline_stage_id' => PipelineStage::first()->id, 'expected_close_date' => now()->subDay()]);
        $quote = app(QuoteService::class)->save(null, ['client_id' => $client->id, 'deal_id' => $deal->id, 'title' => 'Proposta'], [
            ['product_id' => $product->id, 'description' => 'Camiseta', 'quantity' => 1, 'unit_price' => 50],
        ]);
        $tag = Tag::create(['name' => 'VIP', 'color' => '#ff0000']);
        $client->tags()->attach($tag);
        $this->get(route('lead-forms.create'))->assertOk(); // antes de atingir o limite de 1 formulário do plano Básico
        $form = LeadForm::create(['name' => 'Bio', 'slug' => 'bio-abc123', 'title' => 'Fale', 'fields' => [['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'required' => true]]]);
        FinancialEntry::create(['direction' => 'payable', 'description' => 'Aluguel', 'amount' => 900, 'due_date' => now()->subDays(2), 'status' => 'pending']);
        Activity::create(['client_id' => $client->id, 'deal_id' => $deal->id, 'type' => 'call', 'title' => 'Ligação', 'happened_at' => now(), 'next_step' => 'Retornar', 'next_step_at' => now()->addDay()]);

        // Telas Blade mantidas. O CRUD de clientes, produtos, tags, atendimentos
        // e agenda migrou para o Filament (/app/*) e não tem mais rota Blade.
        $pages = [
            '/app', // painel Filament (home autenticada)
            route('dashboard'), // painel KPI em Blade
            route('deals.index'), route('deals.list'), route('deals.show', $deal), route('stages.index'),
            route('activities.index'),
            route('quotes.index'), route('quotes.create'), route('quotes.create', ['deal_id' => $deal->id]), route('quotes.show', $quote), route('quotes.edit', $quote), route('quotes.print', $quote),
            route('finance.index'), route('finance.entries'), route('finance.entries', ['status' => 'overdue']), route('finance.categories'),
            route('lead-forms.index'), route('lead-forms.show', $form), route('lead-forms.edit', $form),
            route('notifications.index'),
        ];

        foreach ($pages as $url) {
            $res = $this->get($url);
            $this->assertSame(200, $res->status(), "GET {$url} → {$res->status()} ".mb_substr((string) $res->exception?->getMessage(), 0, 300));
        }

        $this->get(route('products.search', ['q' => 'cami']))->assertOk()->assertJsonFragment(['name' => 'Camiseta']);

        auth()->logout();
        $this->get(route('lead-forms.public', $form->slug))->assertOk()->assertSee('Fale');
    }

    // ---------------------------------------------------------- multi-tenant

    public function test_users_cannot_access_or_link_records_from_other_accounts(): void
    {
        $alice = User::factory()->create();
        $aliceClient = $this->clientFor($alice);
        $aliceProduct = Product::create(['name' => 'Produto da Alice', 'price' => 10]);
        $aliceTag = Tag::create(['name' => 'Secreta', 'color' => '#000000']);
        $aliceDeal = Deal::create(['client_id' => $aliceClient->id, 'title' => 'Deal da Alice', 'value' => 10]);

        $bob = User::factory()->create();
        $bobClient = $this->clientFor($bob);

        $this->get(route('deals.show', $aliceDeal))->assertNotFound();
        // O CRUD de produtos migrou para o Filament; a busca (mantida) não vaza entre contas.
        $this->get(route('products.search', ['q' => 'Alice']))->assertExactJson([]);

        // Validação de vínculo rejeita IDs de outra conta (falha comum do "exists:tabela,id")
        $this->post(route('deals.store'), ['client_id' => $aliceClient->id, 'title' => 'X', 'value' => 1])
            ->assertSessionHasErrors('client_id');

        $this->post(route('quotes.store'), [
            'client_id' => $bobClient->id, 'title' => 'X',
            'items' => [['product_id' => $aliceProduct->id, 'description' => 'x', 'quantity' => 1, 'unit_price' => 1]],
        ])->assertSessionHasErrors('items.0.product_id');

        // O vínculo de tags a clientes migrou para o Filament; o escopo por conta
        // garante que a tag de outra conta sequer é visível para o Bob.
        $this->actingAs($bob);
        $this->assertFalse(Tag::whereKey($aliceTag->id)->exists());
        $this->assertCount(0, $bobClient->fresh()->tags);
    }

    // ------------------------------------------------------- venda completa

    public function test_approving_a_quote_moves_stock_creates_installments_and_wins_the_deal(): void
    {
        $user = User::factory()->create();
        $client = $this->clientFor($user);
        $product = Product::create(['name' => 'Notebook', 'price' => 3000, 'track_stock' => true]);
        app(StockService::class)->move($product, 'in', 5, 'Compra', 2000);
        $service = Product::create(['type' => 'service', 'name' => 'Instalação', 'price' => 100]);
        $deal = Deal::create(['client_id' => $client->id, 'title' => 'Notebooks', 'value' => 6000]);

        $this->post(route('quotes.store'), [
            'client_id' => $client->id, 'deal_id' => $deal->id, 'title' => '2 notebooks', 'discount' => 100,
            'items' => [
                ['product_id' => $product->id, 'description' => 'Notebook', 'quantity' => 2, 'unit_price' => 3000],
                ['product_id' => $service->id, 'description' => 'Instalação', 'quantity' => 1, 'unit_price' => 100.01],
            ],
        ])->assertRedirect();

        $quote = Quote::firstOrFail();
        $this->assertSame('ORC-'.now()->format('Y').'-0001', $quote->number);
        $this->assertEquals(6100.01, (float) $quote->subtotal);
        $this->assertEquals(6000.01, (float) $quote->total);

        $this->post(route('quotes.approve', $quote), ['installments' => 3, 'first_due_date' => now()->toDateString(), 'payment_method' => 'pix'])
            ->assertSessionHas('success');

        $this->assertSame('approved', $quote->fresh()->status);
        $this->assertEquals(3, (float) $product->fresh()->stock);
        $this->assertEquals(2000, (float) $product->fresh()->cost, 'entrada com custo atualiza o custo do produto');

        $entries = FinancialEntry::orderBy('due_date')->get();
        $this->assertCount(3, $entries);
        $this->assertEquals(6000.01, round((float) $entries->sum('amount'), 2), 'a soma das parcelas bate com o total');
        $this->assertEquals(2000.01, (float) $entries[0]->amount);
        $this->assertSame(now()->addMonthsNoOverflow(2)->toDateString(), $entries[2]->due_date->toDateString());

        $this->assertSame(Deal::STATUS_WON, $deal->fresh()->status);
        $this->assertTrue(Activity::where('client_id', $client->id)->where('type', 'proposal')->exists());

        // Aprovado não pode ser editado nem aprovado de novo
        $this->post(route('quotes.approve', $quote), ['installments' => 1, 'first_due_date' => now()->toDateString()])->assertSessionHas('error');
        $this->get(route('quotes.edit', $quote))->assertRedirect(route('quotes.show', $quote));
        $this->assertCount(3, FinancialEntry::all());
    }

    public function test_quote_approval_is_blocked_when_stock_is_insufficient_unless_allowed(): void
    {
        $user = User::factory()->create();
        $client = $this->clientFor($user);
        $product = Product::create(['name' => 'Cadeira', 'price' => 200, 'track_stock' => true]);
        app(StockService::class)->move($product, 'in', 1);

        $quote = app(QuoteService::class)->save(null, ['client_id' => $client->id, 'title' => 'Cadeiras'], [
            ['product_id' => $product->id, 'description' => 'Cadeira', 'quantity' => 4, 'unit_price' => 200],
        ]);

        $this->post(route('quotes.approve', $quote), ['installments' => 1, 'first_due_date' => now()->toDateString()])
            ->assertSessionHas('error');
        $this->assertSame('draft', $quote->fresh()->status);
        $this->assertEquals(1, (float) $product->fresh()->stock);
        $this->assertCount(0, FinancialEntry::all(), 'transação desfeita: nada foi lançado');

        $this->post(route('quotes.approve', $quote), ['installments' => 1, 'first_due_date' => now()->toDateString(), 'allow_negative_stock' => 1])
            ->assertSessionHas('success');
        $this->assertEquals(-3, (float) $product->fresh()->stock);
    }

    // -------------------------------------------------------------- limites

    public function test_basic_plan_limits_apply_to_new_resources(): void
    {
        $user = User::factory()->create();
        $client = $this->clientFor($user);

        LeadForm::create(['name' => 'Primeiro', 'slug' => 'primeiro-x1', 'title' => 'T', 'fields' => []]);
        $res = $this->get(route('lead-forms.create'));
        $this->assertSame(302, $res->status(), 'lead-forms.create: '.mb_substr((string) $res->exception?->getMessage(), 0, 300));
        $res->assertRedirect(route('lead-forms.index'))->assertSessionHas('error');

        for ($i = 0; $i < 5; $i++) {
            app(QuoteService::class)->save(null, ['client_id' => $client->id, 'title' => "Q{$i}"], [
                ['description' => 'Item', 'quantity' => 1, 'unit_price' => 10],
            ]);
        }
        $this->post(route('quotes.store'), [])->assertRedirect(route('quotes.index'))->assertSessionHas('error');
    }

    // ------------------------------------------------------------- captação

    public function test_public_lead_form_creates_client_deal_and_deduplicates_by_email(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $form = LeadForm::create([
            'name' => 'Site', 'slug' => 'site-abc123', 'title' => 'Contato', 'default_source' => 'instagram', 'create_deal' => true,
            'fields' => [
                ['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'required' => true],
                ['name' => 'company', 'label' => 'Empresa', 'type' => 'text', 'required' => false],
            ],
        ]);
        auth()->logout();

        $payload = ['data' => ['name' => 'Maria', 'email' => 'MARIA@ex.com', 'company' => 'Padaria Sol'], 'lgpd_consent' => 1, 'utm_source' => 'instagram'];

        $this->from(route('lead-forms.public', $form->slug))->post(route('lead-forms.submit', $form->slug), $payload)
            ->assertSessionHas('lead_success');

        $client = Client::withoutGlobalScope('owned')->where('user_id', $owner->id)->firstOrFail();
        $this->assertSame('maria@ex.com', $client->email);
        $this->assertSame('instagram', $client->source);

        $deal = Deal::withoutGlobalScope('owned')->where('client_id', $client->id)->firstOrFail();
        $this->assertSame('Lead: Padaria Sol', $deal->title);
        $this->assertSame($owner->id, $deal->user_id);

        // Segundo envio com o mesmo e-mail: não duplica o cliente
        $this->post(route('lead-forms.submit', $form->slug), $payload)->assertSessionHas('lead_success');
        $this->assertSame(1, Client::withoutGlobalScope('owned')->where('user_id', $owner->id)->count());
        $this->assertSame(2, LeadForm::withoutGlobalScope('owned')->find($form->id)->submissions_count);

        // Validação: consentimento LGPD e campos obrigatórios
        $this->post(route('lead-forms.submit', $form->slug), ['data' => ['name' => '']])
            ->assertSessionHasErrors(['data.name', 'data.email', 'lgpd_consent']);

        // Honeypot: robô "tem sucesso" mas nada é gravado
        $this->post(route('lead-forms.submit', $form->slug), $payload + ['website_url' => 'http://spam'])->assertSessionHas('lead_success');
        $this->assertSame(2, LeadFormSubmission::withoutGlobalScope('owned')->count());

        // Formulário pausado some do ar
        LeadForm::withoutGlobalScope('owned')->whereKey($form->id)->update(['is_active' => false]);
        $this->get(route('lead-forms.public', $form->slug))->assertNotFound();
    }

    public function test_lead_is_kept_but_client_not_created_when_plan_limit_is_reached(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        for ($i = 0; $i < 10; $i++) {
            Client::create(['name' => "C{$i}"]);
        }
        $form = LeadForm::create(['name' => 'F', 'slug' => 'f-limit1', 'title' => 'T', 'fields' => [['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'required' => true]]]);
        auth()->logout();

        $this->post(route('lead-forms.submit', $form->slug), ['data' => ['email' => 'novo@ex.com'], 'lgpd_consent' => 1])
            ->assertSessionHas('lead_success');

        $submission = LeadFormSubmission::withoutGlobalScope('owned')->firstOrFail();
        $this->assertNull($submission->client_id);
        $this->assertStringContainsString('Limite', (string) $submission->note);
        $this->assertSame(10, Client::withoutGlobalScope('owned')->where('user_id', $owner->id)->count());
    }

    // ----------------------------------------------------- CRM / financeiro

    public function test_activity_next_step_becomes_a_reminder_and_deal_can_be_moved_and_lost(): void
    {
        $user = User::factory()->create();
        $client = $this->clientFor($user);
        PipelineStage::ensureDefaultsFor($user->account_id);
        $stages = PipelineStage::ordered()->get();
        $deal = Deal::create(['client_id' => $client->id, 'title' => 'App', 'value' => 5000, 'pipeline_stage_id' => $stages[0]->id]);

        $this->post(route('activities.store'), [
            'client_id' => $client->id, 'deal_id' => $deal->id, 'type' => 'meeting', 'title' => 'Reunião de briefing',
            'next_step' => 'Enviar proposta', 'next_step_at' => now()->addDays(2)->format('Y-m-d H:i'),
        ])->assertSessionHas('success');

        $reminder = Reminder::firstOrFail();
        $this->assertSame('Próximo passo: Enviar proposta', $reminder->title);
        $this->assertSame($client->id, $reminder->client_id);
        $this->assertSame($reminder->id, Activity::firstOrFail()->reminder_id);

        $this->patchJson(route('deals.move', $deal), ['pipeline_stage_id' => $stages[3]->id])->assertOk()->assertJson(['ok' => true]);
        $this->assertSame($stages[3]->probability, $deal->fresh()->probability);

        $this->post(route('deals.lose', $deal), [])->assertSessionHasErrors('lost_reason');
        $this->post(route('deals.lose', $deal), ['lost_reason' => 'Preço'])->assertSessionHas('success');
        $this->assertSame(Deal::STATUS_LOST, $deal->fresh()->status);
    }

    public function test_finance_entries_can_be_settled_and_overdue_is_computed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('finance.store'), ['direction' => 'payable', 'description' => 'Internet', 'amount' => 100, 'due_date' => now()->subDays(3)->toDateString(), 'installments' => 1])
            ->assertSessionHas('success');
        $entry = FinancialEntry::firstOrFail();

        $this->assertTrue($entry->isOverdue());
        $this->assertSame(1, FinancialEntry::overdue()->count());

        $this->post(route('finance.settle', $entry), ['paid_at' => now()->toDateString(), 'paid_amount' => 99.5, 'payment_method' => 'pix'])->assertSessionHas('success');
        $entry->refresh();
        $this->assertSame('paid', $entry->status);
        $this->assertSame(0, FinancialEntry::overdue()->count());

        $summary = app(FinanceService::class)->monthSummary();
        $this->assertEquals(99.5, $summary['paid']);
        $this->assertEquals(-99.5, $summary['balance']);
    }

    public function test_stock_adjust_sets_absolute_balance_and_logs_movement(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::create(['name' => 'Parafuso', 'price' => 1, 'track_stock' => true]);

        $this->post(route('products.movement', $product), ['type' => 'in', 'quantity' => 100, 'unit_cost' => 0.3, 'document' => 'NF 123'])->assertSessionHas('success');
        $this->post(route('products.movement', $product), ['type' => 'out', 'quantity' => 10])->assertSessionHas('success');
        $this->post(route('products.movement', $product), ['type' => 'adjust', 'quantity' => 85])->assertSessionHas('success');

        $this->assertEquals(85, (float) $product->fresh()->stock);
        $this->assertSame(['in', 'out', 'adjust'], $product->movements()->reorder()->oldest()->pluck('type')->all());
        $this->assertEquals(85, (float) $product->movements()->reorder()->oldest()->get()->last()->balance_after);
    }
}
