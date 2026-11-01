<?php

declare(strict_types=1);

use Tests\Support\Personas;

/** AC-22: the "Financeiro" group in the Filament panel and in the Blade sidebar. */
const FINANCEIRO_ITEMS = ['Visão geral financeira', 'Contas a pagar/receber', 'Minha assinatura', 'Histórico de pagamentos'];

const SIDEBAR_ITEMS = [
    'Visão geral' => 'finance.index',
    'Contas a pagar/receber' => 'finance.entries',
    'Minha assinatura' => 'billing.index',
    'Histórico de pagamentos' => 'billing.invoices',
];

function expectPanelItems($response, array $visible): void
{
    foreach (FINANCEIRO_ITEMS as $label) {
        in_array($label, $visible, true) ? $response->assertSee($label) : $response->assertDontSee($label);
    }
}

function expectSidebarLinks($response, array $visibleRoutes): void
{
    foreach (SIDEBAR_ITEMS as $route) {
        $link = 'href="'.route($route).'"';
        in_array($route, $visibleRoutes, true) ? $response->assertSee($link, false) : $response->assertDontSee($link, false);
    }
}

it('shows the four Financeiro items to the owner', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);

    expectPanelItems($this->get('/app')->assertOk(), FINANCEIRO_ITEMS);
    expectSidebarLinks($this->get(route('reminders.index'))->assertOk(), array_values(SIDEBAR_ITEMS));
});

it('shows only the finance module to a manager', function (): void {
    $manager = Personas::manager(Personas::owner()->account);
    $this->actingAs($manager);

    expectPanelItems($this->get('/app')->assertOk(), ['Visão geral financeira', 'Contas a pagar/receber']);
    expectSidebarLinks($this->get(route('reminders.index'))->assertOk(), ['finance.index', 'finance.entries']);
});

it('hides the whole Financeiro group from a collaborator', function (): void {
    $collaborator = Personas::collaborator(Personas::owner()->account);
    $this->actingAs($collaborator);

    expectPanelItems($this->get('/app')->assertOk(), []);
    expectSidebarLinks($this->get(route('reminders.index'))->assertOk(), []);
});

it('removes the old "Assinatura" entry from the Conta group', function (): void {
    $this->actingAs(Personas::owner());

    $this->get('/app')->assertOk()->assertSee('Minha assinatura')->assertDontSee('>Assinatura<', false);
});
