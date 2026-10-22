<?php

declare(strict_types=1);

namespace App\Filament\Resources\Deals\Tables;

use App\Models\Deal;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable()->sortable()->weight('medium')
                    ->tooltip('Nome da oportunidade de negócio'),
                TextColumn::make('client.name')->label('Cliente')->searchable()->toggleable()
                    ->placeholder('Sem cliente vinculado'),
                TextColumn::make('stage.name')->label('Etapa')->badge()->toggleable()
                    ->placeholder('Sem etapa'),
                TextColumn::make('value')->label('Valor')->money('BRL')->sortable()
                    ->tooltip('Valor estimado do negócio')->placeholder('A definir'),
                TextColumn::make('probability')->label('Prob.')->suffix('%')->toggleable()
                    ->tooltip('Probabilidade de fechamento da oportunidade'),
                TextColumn::make('status')->label('Situação')->badge()
                    ->tooltip('Situação atual da oportunidade no funil')
                    ->formatStateUsing(fn (string $state): string => Deal::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Deal::STATUS_WON => 'success',
                        Deal::STATUS_LOST => 'danger',
                        default => 'info',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('Situação')
                    ->placeholder('Todas as situações')
                    ->options(Deal::STATUS_LABELS),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-funnel')
            ->emptyStateHeading('Nenhuma oportunidade no funil')
            ->emptyStateDescription('Comece a registrar suas negociações para acompanhar o avanço de cada venda e aumentar suas chances de fechamento.');
    }
}
