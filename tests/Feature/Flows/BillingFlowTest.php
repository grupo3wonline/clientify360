<?php

declare(strict_types=1);

use App\Models\Client;
use App\Services\SubscriptionService;
use App\Support\AccountCache;
use Laravel\Cashier\Subscription;
use Tests\Support\Personas;
use Tests\Support\Subscriptions;

/*
 * Upgrade -> Profissional features -> cancel at period end -> grace period ->
 * back to Básico. Stripe calls are stubbed on a partial mock; the plan logic
 * (limits, features, grace period) runs for real on local Cashier rows.
 */

it('upgrades, cancels and falls back to Básico without calling Stripe', function (): void {
    config(['plans.professional.stripe_price_id' => 'price_test_2990']);
    $owner = Personas::owner();
    for ($i = 1; $i <= 10; $i++) {
        Client::factory()->create(['account_id' => $owner->account_id, 'user_id' => $owner->id]);
    }

    $billing = Mockery::mock(SubscriptionService::class, [new AccountCache])->makePartial();
    $billing->shouldReceive('createCheckoutSession')->once()->andReturn('https://checkout.stripe.com/c/pay/cs_test_flow');
    $billing->shouldReceive('syncSubscriptionFromStripe')->once()->andReturnUsing(fn () => Subscriptions::makeProfessional($owner->account));
    $billing->shouldReceive('cancelSubscription')->once()->andReturnUsing(function () use ($owner) {
        $endsAt = now()->addDays(10);
        Subscription::query()->where('account_id', $owner->account_id)->update(['ends_at' => $endsAt]);

        return $endsAt;
    });
    app()->instance(SubscriptionService::class, $billing);

    $this->actingAs($owner);

    // Básico: the 11th client and the export are blocked.
    $this->get(route('clients.create'))->assertRedirect(route('clients.index'));
    $this->get(route('export.clients'))->assertForbidden();

    // Upgrade through Checkout and the return URL.
    $this->post(route('billing.subscribe'))->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_flow');
    $this->get(route('billing.index', ['checkout' => 'success']))->assertOk();

    $this->actingAs($owner->fresh());
    expect(app(SubscriptionService::class)->isProfessional($owner->fresh()))->toBeTrue();
    $this->get(route('clients.create'))->assertOk();
    $this->get(route('export.clients'))->assertOk();

    // Cancel at period end: still Profissional during the paid period.
    $this->post(route('billing.cancel'))->assertRedirect(route('billing.index'))->assertSessionHas('success');
    $this->actingAs($owner->fresh());
    $this->get(route('export.clients'))->assertOk();

    // After the period ends the account is back on Básico; data stays.
    $this->travel(11)->days();
    $this->actingAs($owner->fresh());
    app(AccountCache::class)->forgetBilling((string) $owner->account_id);

    $this->get(route('export.clients'))->assertForbidden();
    $this->get(route('clients.create'))->assertRedirect(route('clients.index'));
    $this->get(route('clients.index'))->assertOk();
    expect(Client::count())->toBe(10);
});
