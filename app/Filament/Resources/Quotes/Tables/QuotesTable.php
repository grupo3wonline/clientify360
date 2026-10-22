<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes\Tables;

use App\Models\Quote;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Número')->searchable()->sortable()->weight('medium'),
                TextColumn::make('title')->label('Título')->searchable()->toggleable(),
                TextColumn::make('client.name')->label('Cliente')->searchable()->toggleable(),
                TextColumn::make('total')->label('Total')->money('BRL')->sortable(),
                TextColumn::make('status')->label('Situação')->badge()
                    ->formatStateUsing(fn (string $state): string => Quote::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'warning',
                        'sent' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Criado em')->date('d/m/Y')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Situação')->options(Quote::STATUS_LABELS),
            ])
            ->recordActions([
                Action::make('abrir')
                    ->label('Abrir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Quote $record): string => route('quotes.show', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nenhum orçamento criado')
            ->emptyStateDescription('Crie orçamentos pelo módulo de Orçamentos.');
    }
}
