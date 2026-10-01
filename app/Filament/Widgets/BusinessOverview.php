<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Quote;
use App\Services\ClientService;
use App\Services\FinanceService;
use App\Support\Format;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Painel inicial: visão rápida do negócio da conta (reaproveita os services).
 */
class BusinessOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Visão geral';

    protected function getStats(): array
    {
        $openDeals = Deal::open()->get(['value', 'probability']);
        $month = app(FinanceService::class)->monthSummary();
        $clients = app(ClientService::class)->countAll();

        $pipeline = (float) $openDeals->sum('value');
        $forecast = (float) $openDeals->sum(fn (Deal $d): float => $d->weightedValue());

        return [
            Stat::make('Clientes', (string) $clients)
                ->description('Total na sua carteira')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Funil de vendas', Format::money($pipeline))
                ->description($openDeals->count().' em aberto · previsão '.Format::money($forecast))
                ->descriptionIcon('heroicon-m-funnel')
                ->color('info'),

            Stat::make('Recebido no mês', Format::money($month['received']))
                ->description($month['overdue_count'] > 0
                    ? $month['overdue_count'].' lançamento(s) em atraso'
                    : Format::money($month['to_receive']).' a receber')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($month['overdue_count'] > 0 ? 'danger' : 'success'),

            Stat::make('Orçamentos aguardando', (string) Quote::where('status', 'sent')->count())
                ->description('Propostas enviadas em negociação')
                ->descriptionIcon('heroicon-m-document-currency-dollar')
                ->color('warning'),
        ];
    }
}
