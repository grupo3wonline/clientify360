<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Interaction;
use App\Models\Reminder;
use App\Services\FinanceService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly FinanceService $finance,
    ) {}

    public function index(Request $request): View
    {
        $period = $request->query('period', '30');

        $startDate = now()->subDays((int) $period);

        $stats = [
            'total_clients' => Client::count(),
            'new_clients' => Client::where('created_at', '>=', $startDate)->count(),
            'total_interactions' => Interaction::count(),
            'open_interactions' => Interaction::where('status', 'open')->count(),
            'resolved_interactions' => Interaction::where('status', 'resolved')
                ->where('resolved_at', '>=', $startDate)->count(),
            'pending_reminders' => Reminder::where('is_completed', false)
                ->where('remind_at', '>=', now())->count(),
            'overdue_reminders' => Reminder::where('is_completed', false)
                ->where('remind_at', '<', now())->count(),
        ];

        // Clients by score label
        $scoreDistribution = Client::whereNotNull('score_label')
            ->selectRaw('score_label, COUNT(*) as total')
            ->groupBy('score_label')
            ->pluck('total', 'score_label')
            ->toArray();

        // Interactions by type
        $interactionsByType = Interaction::where('created_at', '>=', $startDate)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Interactions by channel
        $interactionsByChannel = Interaction::where('created_at', '>=', $startDate)
            ->selectRaw('channel, COUNT(*) as total')
            ->groupBy('channel')
            ->pluck('total', 'channel')
            ->toArray();

        // Top clients by interactions
        $topClients = Client::withCount(['reminders', 'interactions' => function ($q) use ($startDate): void {
            $q->where('created_at', '>=', $startDate);
        }])->orderByDesc('interactions_count')->take(5)->get();

        // Services demand
        $servicesDemand = [
            'website' => Client::where('needs_website', true)->count(),
            'system' => Client::where('needs_system', true)->count(),
            'marketing' => Client::where('needs_marketing', true)->count(),
            'ecommerce' => Client::where('needs_ecommerce', true)->count(),
            'seo' => Client::where('needs_seo', true)->count(),
            'social_media' => Client::where('needs_social_media', true)->count(),
        ];

        // Desempenho comercial e resumo financeiro do mês (módulos de vendas/financeiro).
        $commercial = $this->reports->commercial($startDate);
        $finance = $this->finance->monthSummary();

        return view('reports.index', compact(
            'stats', 'period', 'scoreDistribution', 'interactionsByType',
            'interactionsByChannel', 'topClients', 'servicesDemand',
            'commercial', 'finance'
        ));
    }
}
