<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Support\Format;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable()->weight('medium'),
                TextColumn::make('type')->label('Tipo')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'service' ? 'Serviço' : 'Produto'),
                TextColumn::make('sku')->label('SKU')->searchable()->toggleable(),
                TextColumn::make('price')->label('Preço')->money('BRL')->sortable(),
                TextColumn::make('stock')->label('Estoque')->sortable()
                    ->formatStateUsing(fn ($state, $record): string => $record->track_stock ? Format::qty($state).' '.$record->unit : '—')
                    ->badge()
                    ->color(fn ($record): string => $record->track_stock && $record->isLowStock() ? 'danger' : 'gray')
                    ->tooltip(fn ($record): ?string => $record->track_stock && $record->isLowStock() ? 'Estoque abaixo do mínimo. Considere repor.' : null),
                IconColumn::make('is_active')->label('Ativo')->boolean()->toggleable()
                    ->tooltip('Indica se o produto está disponível para uso em orçamentos e vendas'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Ativo'),
                TrashedFilter::make()->label('Excluídos'),
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
            ->emptyStateHeading('Nenhum produto cadastrado')
            ->emptyStateDescription('Cadastre seus produtos e serviços para usá-los em orçamentos, vendas e controle de estoque.')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
