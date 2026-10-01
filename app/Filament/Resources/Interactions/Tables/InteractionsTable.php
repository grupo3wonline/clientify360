<?php

declare(strict_types=1);

namespace App\Filament\Resources\Interactions\Tables;

use App\Filament\Resources\Interactions\Schemas\InteractionForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InteractionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->label('Assunto')->searchable()->sortable()->weight('medium'),
                TextColumn::make('client.name')->label('Cliente')->searchable()->toggleable(),
                TextColumn::make('channel')->label('Canal')->badge()
                    ->formatStateUsing(fn (string $state): string => InteractionForm::CHANNELS[$state] ?? $state),
                TextColumn::make('status')->label('Situação')->badge()
                    ->formatStateUsing(fn (string $state): string => InteractionForm::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'resolved', 'closed' => 'success',
                        'in_progress' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')->label('Aberto em')->dateTime('d/m/Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Situação')->options(InteractionForm::STATUSES),
                SelectFilter::make('channel')->label('Canal')->options(InteractionForm::CHANNELS),
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
            ->emptyStateHeading('Nenhum atendimento registrado');
    }
}
