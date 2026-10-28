<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\PlanData;
use App\Exceptions\BillingUnavailableException;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\Exception\ExceptionInterface as StripeException;

/** "Financeiro" → Minha assinatura / Histórico de pagamentos (permissão account.billing). */
final class BillingController extends Controller
{
    private const STRIPE_FAILURE = 'Não foi possível concluir a operação com o Stripe agora. Tente novamente em instantes.';

    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $checkout = $request->query('checkout');

        if ($checkout === 'success') {
            $this->subscriptionService->syncSubscriptionFromStripe($user);
            $this->subscriptionService->forgetBillingCache($user);
            $user->unsetRelation('account');

            $this->subscriptionService->isProfessional($user)
                ? session()->now('success', 'Pagamento confirmado! Bem-vindo ao plano Profissional.')
                : session()->now('info', 'Estamos confirmando seu pagamento. Isso pode levar alguns instantes.');
        } elseif ($checkout === 'cancel') {
            session()->now('info', 'Pagamento não concluído. Nenhuma cobrança foi feita.');
        }

        return view('billing.index', [
            'summary' => $this->subscriptionService->summary($user),
            'plans' => PlanData::catalog(),
            'usage' => $this->subscriptionService->usageReport($user),
        ]);
    }

    public function invoices(Request $request): View
    {
        $invoices = collect();
        $invoicesError = false;

        try {
            $invoices = $this->subscriptionService->invoices($request->user());
        } catch (BillingUnavailableException $e) {
            Log::warning('Stripe invoices unavailable', [
                'account_id' => $request->user()->account_id,
                'error' => $e->getPrevious()?->getMessage(),
            ]);
            $invoicesError = true;
        }

        return view('billing.invoices', compact('invoices', 'invoicesError'));
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($this->subscriptionService->isProfessional($user)) {
            return redirect()->route('billing.index')
                ->with('info', 'Sua conta já está no plano Profissional.');
        }

        $priceId = PlanData::fromConfig('professional')->stripePriceId;

        if ($priceId === null) {
            return redirect()->route('billing.index')
                ->with('error', 'Plano Profissional não configurado. Entre em contato com o suporte.');
        }

        try {
            return redirect($this->subscriptionService->createCheckoutSession($user, $priceId));
        } catch (StripeException $e) {
            Log::error('Stripe checkout failed', ['account_id' => $user->account_id, 'error' => $e->getMessage()]);

            return redirect()->route('billing.index')
                ->with('error', 'Não foi possível iniciar o pagamento agora. Tente novamente em instantes.');
        }
    }

    public function cancel(Request $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $endsAt = $this->subscriptionService->cancelSubscription($user);
        } catch (StripeException $e) {
            return $this->stripeFailure('Stripe cancel failed', $user->account_id, $e);
        }

        $this->subscriptionService->forgetBillingCache($user);
        $until = $endsAt !== null ? ' até '.$endsAt->timezone(config('app.timezone'))->format('d/m/Y') : ' até o fim do período pago';

        return redirect()->route('billing.index')
            ->with('success', "Assinatura cancelada. Você continua no Profissional{$until}; depois a conta volta ao Básico.");
    }

    public function resume(Request $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->subscriptionService->resumeSubscription($user);
        } catch (StripeException $e) {
            return $this->stripeFailure('Stripe resume failed', $user->account_id, $e);
        }

        $this->subscriptionService->forgetBillingCache($user);

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

        try {
            return redirect($this->subscriptionService->getBillingPortalUrl($user));
        } catch (StripeException $e) {
            return $this->stripeFailure('Stripe portal failed', $user->account_id, $e);
        }
    }

    private function stripeFailure(string $log, ?string $accountId, StripeException $e): RedirectResponse
    {
        Log::error($log, ['account_id' => $accountId, 'error' => $e->getMessage()]);

        return redirect()->route('billing.index')->with('error', self::STRIPE_FAILURE);
    }
}
