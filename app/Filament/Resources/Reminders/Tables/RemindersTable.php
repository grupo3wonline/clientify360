<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reminders\Tables;

use App\Models\Reminder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RemindersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_completed')->label('OK')->boolean(),
                TextColumn::make('title')->label('Título')->searchable()->sortable()->weight('medium'),
                TextColumn::make('client.name')->label('Cliente')->toggleable()->placeholder('—'),
                TextColumn::make('remind_at')->label('Quando')->dateTime('d/m/Y H:i')->sortable()
                    ->color(fn (Reminder $r): string => (!$r->is_completed && $r->remind_at?->isPast()) ? 'danger' : 'gray'),
            ])
            ->filters([
                TernaryFilter::make('is_completed')->label('Concluído'),
            ])
            ->recordActions([
                Action::make('concluir')
                    ->label('Concluir')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (Reminder $r): bool => !$r->is_completed)
                    ->action(fn (Reminder $r) => $r->update(['is_completed' => true, 'completed_at' => now()])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('remind_at')
            ->emptyStateHeading('Nenhum lembrete na agenda');
    }
}
