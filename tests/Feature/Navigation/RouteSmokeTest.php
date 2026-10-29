<?php

declare(strict_types=1);

use App\Data\PlanData;
use App\Data\SubscriptionSummaryData;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Support\AccountCache;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;

/*
 * AC-63: every named GET route of the web group answers the expected status
 * for 4 personas (guest, Básico owner, Profissional owner, collaborator of a
 * Profissional account). The dataset comes from the route table, so a new
 * route is smoke-tested automatically; a route whose parameter has no
 * resolver, or that is not classified below, fails until it is covered.
 */

/** Routes left out of the smoke run, with the reason and the test that covers them. */
const SMOKE_EXCLUDED = [
    'export.download' => 'signed URL sent by e-mail (ExportFlowTest)',
    'verification.verify' => 'signed URL sent by e-mail (EmailVerificationTest, AuthFlowTest)',
    'clients.attachments.show' => 'needs a stored file (AttachmentTest)',
];

const SMOKE_PUBLIC = ['home', 'privacy', 'sitemap', 'robots', 'llms', 'terms.show', 'lead-forms.public'];
const SMOKE_MOVED = ['about', 'services', 'tools', 'contact']; // 301 to the landing anchors
const SMOKE_GUEST_ONLY = ['login', 'register', 'password.request', 'password.reset'];
const SMOKE_PLAN_FEATURE = ['export.clients', 'quotes.pdf', 'service-orders.pdf']; // 403 on Básico

/** @return list<RoutingRoute> */
function smokeRoutes(): array
{
    return collect(Route::getRoutes()->getRoutes())
        ->filter(fn (RoutingRoute $r): bool => in_array('GET', $r->methods(), true)
            && $r->getName() !== null
            && in_array('web', $r->gatherMiddleware(), true)
            && !preg_match('/^(filament|l5-swagger|livewire|storage|debugbar|ignition)\./', $r->getName())
            && !array_key_exists($r->getName(), SMOKE_EXCLUDED))
        ->values()
        ->all();
}

function smokeUrl(RoutingRoute $route, User $owner): string
{
    $values = [];
    foreach ($route->parameterNames() as $name) {
        $values[$name] = match ($name) {
            'slug' => AccountRecords::make('lead_form', $owner)->slug,
            'token' => 'token-de-teste',
            default => AccountRecords::make($name, $owner)->getKey(), // throws for an unknown parameter
        };
    }

    return route((string) $route->getName(), $values);
}

/** @return list<int> */
function smokeExpected(RoutingRoute $route, string $persona): array
{
    $name = (string) $route->getName();
    $permission = collect($route->gatherMiddleware())->first(fn ($m): bool => is_string($m) && str_starts_with($m, 'permission:'));

    return match (true) {
        $name === 'sanctum.csrf-cookie' => [204], // Sanctum's public XSRF cookie endpoint
        in_array($name, SMOKE_PUBLIC, true) => [200],
        in_array($name, SMOKE_MOVED, true) => [301],
        in_array($name, SMOKE_GUEST_ONLY, true) => $persona === 'guest' ? [200] : [302],
        $persona === 'guest' => [302], // to /login
        $persona === 'collaborator' && $permission !== null
            && !in_array(substr($permission, 11), RolesAndPermissionsSeeder::rolePermissions()[User::ROLE_COLLABORATOR], true) => [403],
        $name === 'account.logo.show' => [404], // no logo uploaded
        in_array($name, ['dashboard', 'verification.notice', 'billing.portal'], true) => [302],
        $persona === 'basic' && in_array($name, SMOKE_PLAN_FEATURE, true) => [403],
        default => [200],
    };
}

/** Stripe-backed billing methods stubbed; the plan logic stays real. */
function stubStripeBilling(): void
{
    $mock = Mockery::mock(SubscriptionService::class, [new AccountCache])->makePartial();
    $mock->shouldReceive('summary')->andReturnUsing(fn (User $u) => new SubscriptionSummaryData(
        plan: PlanData::fromConfig($mock->isProfessional($u) ? 'professional' : 'basic'),
        status: 'active', statusLabel: 'Ativa', periodStart: null, periodEnd: null, cancelAtPeriodEnd: false, endsAt: null,
    ));
    $mock->shouldReceive('invoices')->andReturn(collect());
    $mock->shouldReceive('getBillingPortalUrl')->andReturn('https://billing.stripe.com/p/session/test');
    app()->instance(SubscriptionService::class, $mock);
}

it('answers every named GET route with the expected status', function (string $persona): void {
    stubStripeBilling();
    $owner = Personas::owner($persona === 'basic' ? 'basic' : 'professional');
    $actor = match ($persona) {
        'guest' => null,
        'collaborator' => Personas::collaborator($owner->account),
        default => $owner,
    };
    $failures = [];

    foreach (smokeRoutes() as $route) {
        auth()->logout();
        $url = smokeUrl($route, $owner); // records created as guest
        if ($actor !== null) {
            $this->actingAs($actor);
        }

        $response = $this->get($url);
        $status = $response->getStatusCode();
        $expected = smokeExpected($route, $persona);

        if (!in_array($status, $expected, true)) {
            $failures[] = "{$route->getName()} ({$url}): {$status}, expected ".implode('|', $expected);
        } elseif ($persona === 'guest' && $expected === [302] && !str_ends_with((string) $response->headers->get('Location'), '/login')) {
            $failures[] = "{$route->getName()}: guest redirected to {$response->headers->get('Location')}, expected /login";
        }
    }

    expect($failures)->toBe([]);
})->with(['guest', 'basic', 'professional', 'collaborator']);

it('runs the smoke over the whole route table', function (): void {
    $names = collect(smokeRoutes())->map(fn (RoutingRoute $r): string => (string) $r->getName());

    expect($names->count())->toBeGreaterThan(70)
        ->and($names)->toContain('home', 'clients.index', 'billing.index', 'quotes.pdf', 'lead-forms.public')
        ->and(array_intersect(array_keys(SMOKE_EXCLUDED), $names->all()))->toBe([]);
    foreach (array_keys(SMOKE_EXCLUDED) as $excluded) {
        expect(Route::has($excluded))->toBeTrue("excluded route {$excluded} no longer exists");
    }
});
