<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;

/*
 * AC-13: the collaborator and manager matrices, derived from the route table.
 * Every web route guarded by `permission:x` answers 403 to a role without x
 * and is reachable by a role with x. The account is Profissional so no plan
 * gate answers first. Billing stays owner-only.
 */

/** @return list<array{0: string, 1: string, 2: string, 3: list<string>}> [name, method, permission, parameters] */
function permissionGuardedRoutes(): array
{
    return collect(Route::getRoutes()->getRoutes())
        ->map(function (RoutingRoute $route): ?array {
            $permission = collect($route->gatherMiddleware())
                ->first(fn ($m): bool => is_string($m) && str_starts_with($m, 'permission:'));
            if ($permission === null || $route->getName() === null || str_starts_with($route->getName(), 'api.')) {
                return null;
            }

            return [$route->getName(), strtolower($route->methods()[0]), substr($permission, 11), $route->parameterNames()];
        })
        ->filter()
        ->values()
        ->all();
}

/** @param  list<string>  $parameters */
function permissionRouteUrl(string $name, array $parameters, User $owner): ?string
{
    if (count($parameters) > 1) {
        return null; // attachments/{media}: covered in AttachmentTest
    }

    $values = [];
    foreach ($parameters as $parameter) {
        $values[$parameter] = AccountRecords::make($parameter, $owner)->getKey();
    }

    return route($name, $values);
}

it('answers 403 for every route the role lacks and lets it through the ones it has', function (string $role): void {
    $owner = Personas::owner('professional');
    $member = $role === User::ROLE_MANAGER ? Personas::manager($owner->account) : Personas::collaborator($owner->account);
    $granted = RolesAndPermissionsSeeder::rolePermissions()[$role];
    $checked = 0;

    foreach (permissionGuardedRoutes() as [$name, $method, $permission, $parameters]) {
        auth()->logout();
        $url = permissionRouteUrl($name, $parameters, $owner);
        if ($url === null) {
            continue;
        }

        $this->actingAs($member);
        $allowed = in_array($permission, $granted, true);

        if (!$allowed) {
            $this->{$method}($url)->assertForbidden();
        } elseif ($method === 'get' && !in_array($name, ['export.download', 'billing.portal'], true)) {
            // Signed download and the Stripe portal redirect are not plain pages.
            expect($this->get($url)->getStatusCode())->toBeIn([200, 302], "{$role} on {$name}");
        }
        $checked++;
    }

    expect($checked)->toBeGreaterThan(100);
})->with([User::ROLE_COLLABORATOR, User::ROLE_MANAGER]);

it('keeps every billing route owner-only', function (): void {
    $billing = collect(permissionGuardedRoutes())->filter(fn (array $r): bool => str_starts_with($r[0], 'billing.'));

    expect($billing->pluck(2)->unique()->values()->all())->toBe(['account.billing'])
        ->and($billing)->toHaveCount(6)
        ->and(RolesAndPermissionsSeeder::rolePermissions()[User::ROLE_MANAGER])->not->toContain('account.billing')
        ->and(RolesAndPermissionsSeeder::rolePermissions()[User::ROLE_COLLABORATOR])->not->toContain('account.billing');
});

it('gives the owner every permission of the catalog', function (): void {
    expect(RolesAndPermissionsSeeder::rolePermissions()[User::ROLE_OWNER])->toBe(RolesAndPermissionsSeeder::permissions());
});
