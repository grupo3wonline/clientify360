<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BillingController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Sync subscription from Stripe when returning from checkout
        if ($request->query('checkout') === 'success') {
            $this->subscriptionService->syncSubscriptionFromStripe($user);
        }

        $currentPlan = $this->subscriptionService->getCurrentPlan($user);
        $status = $this->subscriptionService->getSubscriptionStatus($user);
        $renewalDate = $this->subscriptionService->getRenewalDate($user);
        $daysUntilRenewal = $this->subscriptionService->getDaysUntilRenewal($user);
        $paymentHistory = $this->subscriptionService->getPaymentHistory($user);
        $plans = config('plans');

        return view('billing.index', compact(
            'currentPlan',
            'status',
            'renewalDate',
            'daysUntilRenewal',
            'paymentHistory',
            'plans'
        ));
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $user = $request->user();
        $priceId = config('plans.professional.stripe_price_id');

        if (empty($priceId)) {
            return redirect()->route('billing.index')
                ->with('error', 'Plano profissional não configurado. Entre em contato com o suporte.');
        }

        $checkoutUrl = $this->subscriptionService->createCheckoutSession($user, $priceId);

        return redirect($checkoutUrl);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->subscriptionService->cancelSubscription($user);

        return redirect()->route('billing.index')
            ->with('success', 'Assinatura cancelada. Você terá acesso até o final do período atual.');
    }

    public function resume(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->subscriptionService->resumeSubscription($user);

        return redirect()->route('billing.index')
            ->with('success', 'Assinatura reativada com sucesso!');
    }

    public function portal(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$this->subscriptionService->hasStripeCustomer($user)) {
            return redirect()->route('billing.index')
                ->with('error', 'Você precisa ter uma assinatura ativa para acessar o portal.');
        }

        $portalUrl = $this->subscriptionService->getBillingPortalUrl($user);

        return redirect($portalUrl);
    }
}
