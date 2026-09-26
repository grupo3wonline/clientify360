<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\LeadFormSubmission;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Reminder;
use App\Services\ClientService;
use App\Services\FinanceService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Antes era uma closure em routes/web.php; virou controller ao passar a agregar os novos módulos. */
final class DashboardController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly ClientService $clients,
        private readonly FinanceService $finance,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $openDeals = Deal::open()->get(['value', 'probability']);
        $month = $this->finance->monthSummary();

        return view('dashboard', [
            'clientCount' => $this->clients->countAll(),
            'currentPlan' => $this->subscriptions->getCurrentPlan($user),
            'clientLimit' => $this->subscriptions->getClientLimit($user),
            'professionalPrice' => (float) config('plans.professional.price'),
            'business' => [
                'pipeline_value' => (float) $openDeals->sum('value'),
                'pipeline_weighted' => (float) $openDeals->sum(fn (Deal $d) => $d->weightedValue()),
                'open_deals' => $openDeals->count(),
                'received_month' => $month['received'],
                'to_receive_month' => $month['to_receive'],
                'overdue_count' => $month['overdue_count'],
                'quotes_waiting' => Quote::where('status', 'sent')->count(),
                'quotes_waiting_value' => (float) Quote::where('status', 'sent')->sum('total'),
                'low_stock' => Product::lowStock()->where('is_active', true)->count(),
                'new_leads_week' => LeadFormSubmission::where('created_at', '>=', now()->subDays(7))->count(),
                'reminders_today' => Reminder::where('is_completed', false)->whereBetween('remind_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            ],
        ]);
    }
}
