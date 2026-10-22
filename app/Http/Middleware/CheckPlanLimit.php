<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uso nas rotas: ->middleware('plan.limit')           // clientes (comportamento original)
 *                ->middleware('plan.limit:products')  // qualquer recurso de config/plans.php
 */
final class CheckPlanLimit
{
    /** Para onde voltar quando o limite estoura, por recurso. */
    private const REDIRECT_ROUTES = [
        'clients' => 'clients.index',
        'products' => 'products.index',
        'quotes_per_month' => 'quotes.index',
        'lead_forms' => 'lead-forms.index',
    ];

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function handle(Request $request, Closure $next, string $resource = 'clients'): Response
    {
        $user = $request->user();

        if ($user === null || $this->subscriptionService->canCreate($user, $resource)) {
            return $next($request);
        }

        $limit = $this->subscriptionService->getLimit($user, $resource);
        $label = SubscriptionService::RESOURCE_LABELS[$resource] ?? $resource;
        $plan = $this->subscriptionService->getCurrentPlan($user)['name'];

        return redirect()->route(self::REDIRECT_ROUTES[$resource] ?? 'dashboard')
            ->with('error', "Você atingiu o limite de {$limit} {$label} do plano {$plan}. Faça upgrade para o plano Profissional para liberar o uso ilimitado.");
    }
}
