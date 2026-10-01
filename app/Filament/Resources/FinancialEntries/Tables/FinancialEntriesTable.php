<?php

declare(strict_types=1);

namespace App\Filament\Resources\FinancialEntries\Tables;

use App\Models\FinancialEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FinancialEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')->label('Descrição')->searchable()->sortable()->weight('medium'),
                TextColumn::make('direction')->label('Tipo')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'receivable' ? 'A receber' : 'A pagar')
                    ->color(fn (string $state): string => $state === 'receivable' ? 'success' : 'warning'),
                TextColumn::make('amount')->label('Valor')->money('BRL')->sortable(),
                TextColumn::make('due_date')->label('Vencimento')->date('d/m/Y')->sortable(),
                TextColumn::make('status')->label('Situação')->badge()
                    ->formatStateUsing(fn ($state, FinancialEntry $record): string => $record->statusLabel())
                    ->color(fn (FinancialEntry $record): string => match ($record->displayStatus()) {
                        'overdue' => 'danger',
                        'paid' => 'success',
                        'canceled' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('direction')->label('Tipo')
                    ->options(['receivable' => 'A receber', 'payable' => 'A pagar']),
                SelectFilter::make('status')->label('Situação')
                    ->options(['pending' => 'Pendente', 'paid' => 'Pago/Recebido', 'canceled' => 'Cancelado']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date')
            ->emptyStateHeading('Nenhum lançamento financeiro');
    }
}
