<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Quote;
use App\Services\ClientService;
use App\Services\FinanceService;
use App\Support\Format;
use App\Traits\CachesPerAccount;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Painel inicial: visão rápida do negócio da conta (reaproveita os services).
 */
class BusinessOverview extends StatsOverviewWidget
{
    use CachesPerAccount;

    protected ?string $heading = 'Visão geral';

    /** Data is cached for 10 minutes, so polling adds nothing and only triggers requests. */
    protected ?string $pollingInterval = null;

    /**
     * Scalars only (the database cache store does not unserialize objects).
     *
     * @return array{clients: int, open_count: int, pipeline: float, forecast: float, quotes_sent: int, month: array<string, float|int>}
     */
    public function overview(): array
    {
        return $this->rememberForAccount('dashboard:overview:'.now()->format('Y-m-d'), 600, function (): array {
            $openDeals = Deal::open()->get(['value', 'probability']);

            return [
                'clients' => app(ClientService::class)->countAll(),
                'open_count' => $openDeals->count(),
                'pipeline' => (float) $openDeals->sum('value'),
                'forecast' => (float) $openDeals->sum(fn (Deal $d): float => $d->weightedValue()),
                'quotes_sent' => Quote::where('status', 'sent')->count(),
                'month' => app(FinanceService::class)->monthSummary(),
            ];
        });
    }

    protected function getStats(): array
    {
        $data = $this->overview();
        $month = $data['month'];
        $pipeline = $data['pipeline'];
        $forecast = $data['forecast'];

        return [
            Stat::make('Clientes', (string) $data['clients'])
                ->description('Total na sua carteira')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Funil de vendas', Format::money($pipeline))
                ->description($data['open_count'].' em aberto · previsão '.Format::money($forecast))
                ->descriptionIcon('heroicon-m-funnel')
                ->color('info'),

            Stat::make('Recebido no mês', Format::money($month['received']))
                ->description($month['overdue_count'] > 0
                    ? $month['overdue_count'].' lançamento(s) em atraso'
                    : Format::money($month['to_receive']).' a receber')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($month['overdue_count'] > 0 ? 'danger' : 'success'),

            Stat::make('Orçamentos aguardando', (string) $data['quotes_sent'])
                ->description('Propostas enviadas em negociação')
                ->descriptionIcon('heroicon-m-document-currency-dollar')
                ->color('warning'),
        ];
    }
}
