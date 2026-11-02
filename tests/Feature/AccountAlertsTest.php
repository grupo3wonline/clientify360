<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\AccountAlertService;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use App\Support\AccountCache;
use Mockery\MockInterface;
use Tests\Support\Personas;
use Tests\Support\Subscriptions;

beforeEach(function (): void {
    config(['security.password.max_age_days' => 90, 'security.password.warning_days' => 7, 'security.subscription_warning_days' => 7]);
    $this->travelTo('2026-11-02 12:00:00');
});

function alertUserWithPasswordAge(int $days): User
{
    return User::factory()->create(['password_changed_at' => now()->subDays($days)]);
}

/** Puts a cached Stripe summary in place so the alert never calls Stripe in tests. */
function alertFakeBillingSummary(User $user, string $status, ?int $daysToEnd, bool $cancelAtPeriodEnd = false): void
{
    $end = $daysToEnd === null ? null : now()->addDays($daysToEnd)->startOfDay()->addHours(10);
    app(AccountCache::class)->putBilling((string) $user->account_id, 'summary', [
        'plan' => 'professional',
        'status' => $status,
        'period_start' => now()->subDays(20)->getTimestamp(),
        'period_end' => $end?->getTimestamp(),
        'cancel_at_period_end' => $cancelAtPeriodEnd,
        'ends_at' => $cancelAtPeriodEnd ? $end?->getTimestamp() : null,
    ], 600);
}

// ------------------------------------------------------------ password alert

it('has no password alert before the last 7 days', function (): void {
    expect(app(AccountAlertService::class)->passwordAlert(alertUserWithPasswordAge(82)))->toBeNull();
});

it('warns from 7 days before, with the right wording', function (int $age, string $expects): void {
    $alert = app(AccountAlertService::class)->passwordAlert(alertUserWithPasswordAge($age));

    expect($alert)->not->toBeNull()
        ->and($alert['type'])->toBe('password_expiring')
        ->and($alert['message'])->toContain($expects);
})->with([
    '7 days' => [83, 'expira em 7 dias'],
    '2 days' => [88, 'expira em 2 dias'],
    'tomorrow' => [89, 'expira amanhã'],
    'today' => [90, 'expira hoje'],
]);

it('turns red for the last two days', function (int $age, string $level): void {
    expect(app(AccountAlertService::class)->passwordAlert(alertUserWithPasswordAge($age))['level'])->toBe($level);
})->with([[83, 'warning'], [88, 'warning'], [89, 'danger'], [90, 'danger']]);

it('has no reminder once expired: the block takes over', function (): void {
    expect(app(AccountAlertService::class)->passwordAlert(alertUserWithPasswordAge(91)))->toBeNull();
});

it('puts the password reminder in the notification bell and counts it in the badge', function (): void {
    $user = alertUserWithPasswordAge(85);
    $this->actingAs($user);

    $notifications = app(NotificationService::class)->for();

    expect($notifications->pluck('type'))->toContain('password_expiring')
        ->and(app(NotificationService::class)->count())->toBe(1);

    $this->get(route('notifications.index'))->assertOk()->assertSee('Sua senha vai expirar')->assertSee('expira em 5 dias');
});

it('shows the banner on the app pages and links to the password form', function (): void {
    $this->actingAs(alertUserWithPasswordAge(86))->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('data-account-alerts', false)
        ->assertSee('Sua senha vai expirar')
        ->assertSee('id="atualizar-senha"', false);
});

it('shows the banner on the panel too', function (): void {
    $this->actingAs(alertUserWithPasswordAge(86))->get('/app')->assertOk()->assertSee('Sua senha vai expirar');
});

it('shows nothing when there is nothing to warn about', function (): void {
    $this->actingAs(alertUserWithPasswordAge(10))->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('data-account-alerts', false);
});

it('lets the user dismiss today\'s password reminder from the bell', function (): void {
    $user = alertUserWithPasswordAge(85);
    $this->actingAs($user);
    $key = app(NotificationService::class)->for()->firstWhere('type', 'password_expiring')['id'];

    $this->post(route('notifications.markRead', $key))->assertRedirect(route('notifications.index'));

    app()->forgetInstance(NotificationService::class);
    expect(app(NotificationService::class)->for()->pluck('type'))->not->toContain('password_expiring');
});

it('brings the reminder back the next day with the new count', function (): void {
    $user = alertUserWithPasswordAge(85);
    $this->actingAs($user);
    $key = app(NotificationService::class)->for()->firstWhere('type', 'password_expiring')['id'];
    $this->post(route('notifications.markRead', $key));

    $this->travelTo('2026-11-03 09:00:00');
    app()->forgetInstance(NotificationService::class);
    $again = app(NotificationService::class)->for()->firstWhere('type', 'password_expiring');

    expect($again)->not->toBeNull()
        ->and($again['message'])->toContain('expira em 4 dias');
});

// ------------------------------------------------------- subscription alert

it('warns the owner 7 days before the renewal', function (): void {
    $owner = User::factory()->create();
    Subscriptions::makeProfessional($owner->account);
    alertFakeBillingSummary($owner, 'active', 7);

    $alert = app(AccountAlertService::class)->subscriptionAlert($owner->fresh());

    expect($alert)->not->toBeNull()
        ->and($alert['type'])->toBe('subscription_renewal')
        ->and($alert['title'])->toBe('Sua assinatura renova em breve')
        ->and($alert['message'])->toContain('renova em 7 dias')
        ->and($alert['level'])->toBe('warning')
        ->and($alert['url'])->toBe(route('billing.index'));
});

it('does not warn before the window or after the renewal', function (int $days): void {
    $owner = User::factory()->create();
    Subscriptions::makeProfessional($owner->account);
    alertFakeBillingSummary($owner, 'active', $days);

    expect(app(AccountAlertService::class)->subscriptionAlert($owner->fresh()))->toBeNull();
})->with(['8 days away' => [8], '30 days away' => [30], 'already past' => [-1]]);

it('says "hoje" and "amanhã" at the edges', function (int $days, string $word): void {
    $owner = User::factory()->create();
    Subscriptions::makeProfessional($owner->account);
    alertFakeBillingSummary($owner, 'active', $days);

    expect(app(AccountAlertService::class)->subscriptionAlert($owner->fresh())['message'])->toContain("renova {$word}");
})->with([[0, 'hoje'], [1, 'amanhã']]);

it('warns louder that a canceled plan is about to end', function (): void {
    $owner = User::factory()->create();
    Subscriptions::makeCanceling($owner->account, now()->addDays(3));
    alertFakeBillingSummary($owner, 'canceling', 3, cancelAtPeriodEnd: true);

    $alert = app(AccountAlertService::class)->subscriptionAlert($owner->fresh());

    expect($alert['title'])->toBe('Sua assinatura vai encerrar')
        ->and($alert['level'])->toBe('danger')
        ->and($alert['message'])->toContain('encerra em 3 dias');
});

it('flags a pending payment near the end of the period', function (): void {
    $owner = User::factory()->create();
    Subscriptions::makeProfessional($owner->account);
    alertFakeBillingSummary($owner, 'past_due', 2);

    $alert = app(AccountAlertService::class)->subscriptionAlert($owner->fresh());

    expect($alert['title'])->toBe('Pagamento pendente')->and($alert['level'])->toBe('danger');
});

it('never warns about a renewal on the free plan', function (): void {
    expect(app(AccountAlertService::class)->subscriptionAlert(User::factory()->create()))->toBeNull();
});

it('only the owner gets the billing reminder, not teammates who cannot act on it', function (): void {
    $owner = User::factory()->create();
    Subscriptions::makeProfessional($owner->account);
    alertFakeBillingSummary($owner, 'active', 5);

    $manager = Personas::manager($owner->account);
    $collaborator = Personas::collaborator($owner->account);

    expect(app(AccountAlertService::class)->subscriptionAlert($owner->fresh()))->not->toBeNull()
        ->and(app(AccountAlertService::class)->subscriptionAlert($manager))->toBeNull()
        ->and(app(AccountAlertService::class)->subscriptionAlert($collaborator))->toBeNull();
});

it('uses a new alert key for each billing cycle', function (): void {
    $owner = User::factory()->create();
    Subscriptions::makeProfessional($owner->account);

    alertFakeBillingSummary($owner, 'active', 5);
    $first = app(AccountAlertService::class)->subscriptionAlert($owner->fresh())['id'];

    alertFakeBillingSummary($owner, 'active', 6);
    $second = app(AccountAlertService::class)->subscriptionAlert($owner->fresh())['id'];

    expect($first)->not->toBe($second)->and($first)->toStartWith('subscription-renewal-');
});

it('puts the renewal reminder in the owner\'s notification bell', function (): void {
    $owner = User::factory()->create();
    Subscriptions::makeProfessional($owner->account);
    alertFakeBillingSummary($owner, 'active', 4);
    $this->actingAs($owner->fresh());

    $this->get(route('notifications.index'))->assertOk()->assertSee('Sua assinatura renova em breve')->assertSee('renova em 4 dias');
});

it('does not break any page when billing is unreachable', function (): void {
    $owner = User::factory()->create();
    Subscriptions::makeProfessional($owner->account);
    // Partial mock: only the Stripe-backed summary fails, every other method stays real.
    $this->partialMock(SubscriptionService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('summary')->andThrow(new RuntimeException('Stripe fora do ar'));
    });

    expect(app(AccountAlertService::class)->subscriptionAlert($owner->fresh()))->toBeNull();
    $this->actingAs($owner->fresh())->get(route('profile.edit'))->assertOk();
});
