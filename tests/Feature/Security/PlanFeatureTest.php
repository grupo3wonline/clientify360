<?php

declare(strict_types=1);

use App\Services\SubscriptionService;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\Support\AccountRecords;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

/*
 * AC-18: every route behind `plan.feature:x` (export, PDF, API, docs) answers
 * 403 with the feature message on Básico. AI is gated in AiController
 * (PlanFeatureGateTest) and the docs through the l5-swagger middleware.
 */

/** @return list<array{0: RoutingRoute, 1: string}> */
function planFeatureRoutes(): array
{
    return collect(Route::getRoutes()->getRoutes())
        ->map(function (RoutingRoute $route): ?array {
            $gate = collect($route->gatherMiddleware())->first(fn ($m): bool => is_string($m) && str_starts_with($m, 'plan.feature:'));

            return $gate === null ? null : [$route, substr($gate, 13)];
        })
        ->filter()
        ->values()
        ->all();
}

it('answers 403 with the feature message on every plan.feature route for Básico', function (): void {
    $owner = Personas::owner('basic');
    $token = ApiTokens::for($owner);
    $plans = app(SubscriptionService::class);
    $features = [];

    foreach (planFeatureRoutes() as [$route, $feature]) {
        $parameters = [];
        foreach ($route->parameterNames() as $name) {
            $parameters[$name] = AccountRecords::make($name, $owner)->getKey();
        }
        $url = route((string) $route->getName(), $parameters);
        $method = strtolower($route->methods()[0]).'Json';

        $request = str_starts_with((string) $route->getName(), 'api.')
            ? $this->withToken($token)
            : $this->actingAs($owner);

        $request->{$method}($url)
            ->assertForbidden()
            ->assertJson(['message' => $plans->featureMessage($feature)]);

        $this->app['auth']->forgetGuards();
        $this->flushHeaders();
        $features[] = $feature;
    }

    expect(array_values(array_unique($features)))->toEqualCanonicalizing(['api', 'export', 'pdf']);
});

it('protects the API docs with the plan gate', function (): void {
    expect(config('l5-swagger.defaults.routes.middleware.api'))->toContain('plan.feature:api')
        ->and(config('l5-swagger.defaults.routes.middleware.docs'))->toContain('plan.feature:api');
});
