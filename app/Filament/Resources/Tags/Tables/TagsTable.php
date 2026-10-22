<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('Cor')
                    ->tooltip('Cor usada para destacar a etiqueta nas listagens'),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('clients_count')
                    ->label('Clientes')
                    ->counts('clients')
                    ->badge()
                    ->tooltip('Quantidade de clientes marcados com esta etiqueta'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading('Nenhuma etiqueta criada')
            ->emptyStateDescription('Crie etiquetas para organizar e segmentar seus clientes com rapidez. Por exemplo: "VIP", "Lead quente" ou "Inadimplente".')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Criar primeira etiqueta')
                    ->icon(Heroicon::OutlinedPlus),
            ]);
    }
}
