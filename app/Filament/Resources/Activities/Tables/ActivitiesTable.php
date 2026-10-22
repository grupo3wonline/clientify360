<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Tables;

use App\Models\Activity;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('happened_at')->label('Quando')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('type')->label('Tipo')->badge()
                    ->formatStateUsing(fn (string $state): string => Activity::TYPES[$state]['label'] ?? $state),
                TextColumn::make('title')->label('Título')->searchable()->weight('medium')->wrap(),
                TextColumn::make('client.name')->label('Cliente')->searchable()->toggleable(),
                TextColumn::make('outcome')->label('Resultado')->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? (Activity::OUTCOMES[$state] ?? $state) : '—')
                    ->color(fn (?string $state): string => match ($state) {
                        'positive' => 'success',
                        'negative' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipo')
                    ->options(fn (): array => collect(Activity::TYPES)->map(fn ($t) => $t['label'])->all()),
            ])
            ->defaultSort('happened_at', 'desc')
            ->emptyStateHeading('Nenhuma atividade registrada');
    }
}
