<?php

declare(strict_types=1);

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable()->sortable()->weight('medium'),
                TextColumn::make('client.name')->label('Cliente')->searchable()->toggleable(),
                TextColumn::make('starts_at')->label('Início')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('type')->label('Tipo')->badge()
                    ->formatStateUsing(fn (string $state): string => Appointment::TYPES[$state] ?? $state),
                TextColumn::make('status')->label('Situação')->badge()
                    ->formatStateUsing(fn (string $state): string => Appointment::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'info',
                        'done' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipo')->options(Appointment::TYPES),
                SelectFilter::make('status')->label('Situação')->options(Appointment::STATUSES),
            ])
            ->recordActions([
                Action::make('abrir')
                    ->label('Abrir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Appointment $record): string => route('appointments.show', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->emptyStateHeading('Nenhum agendamento criado')
            ->emptyStateDescription('Crie agendamentos pelo módulo de Agendamentos.');
    }
}
