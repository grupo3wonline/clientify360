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

        // A blocked upload goes back to the client's "Anexos" card.
        if ($resource === 'client_attachments') {
            return back()->withFragment('anexos')
                ->with('error', $this->subscriptionService->limitMessage($user, $resource));
        }

        return redirect()->route(self::REDIRECT_ROUTES[$resource] ?? 'dashboard')
            ->with('error', $this->subscriptionService->limitMessage($user, $resource));
    }
}
