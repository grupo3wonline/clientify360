<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks Profissional-only features (config/plans.php features_enabled).
 * Uso nas rotas: ->middleware('plan.feature:export')
 */
final class EnsurePlanFeature
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        // Guests are handled by the auth middleware.
        if ($user === null || $this->subscriptionService->hasFeature($user, $feature)) {
            return $next($request);
        }

        $message = $this->subscriptionService->featureMessage($feature);

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        abort(403, $message);
    }
}
