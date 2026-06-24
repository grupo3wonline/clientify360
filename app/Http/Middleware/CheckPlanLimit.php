<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ClientService;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckPlanLimit
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly ClientService $clientService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $currentCount = $this->clientService->countAll();

        if (!$this->subscriptionService->canCreateClient($user, $currentCount)) {
            $limit = $this->subscriptionService->getClientLimit($user);

            return redirect()->route('clients.index')
                ->with('error', "Você atingiu o limite de {$limit} clientes do plano Básico. Faça upgrade para o plano Profissional para cadastrar clientes ilimitados.");
        }

        return $next($request);
    }
}
