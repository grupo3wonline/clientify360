<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Without a webhook secret Cashier skips signature verification. In production
 * that would accept forged events, so the request is rejected with 503 (Stripe
 * retries, so no event is lost once the secret is configured).
 */
final class EnsureStripeWebhookIsConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() && blank(config('cashier.webhook.secret'))) {
            Log::critical('Stripe webhook secret missing in production; webhook rejected.');

            return response('Webhook não configurado.', 503);
        }

        return $next($request);
    }
}
