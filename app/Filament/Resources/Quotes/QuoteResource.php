<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes;

use App\Filament\Resources\Quotes\Pages\ListQuotes;
use App\Filament\Resources\Quotes\Tables\QuotesTable;
use App\Models\Quote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Orçamentos no painel ficam como consulta (lista). A criação/edição e a
 * aprovação-como-venda continuam no fluxo Blade dedicado (QuoteService),
 * que tem o construtor de itens e a baixa de estoque/geração de contas.
 */
class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Comercial';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'orçamento';

    protected static ?string $pluralModelLabel = 'orçamentos';

    protected static ?string $navigationLabel = 'Orçamentos';

    protected static ?string $recordTitleAttribute = 'number';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return QuotesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuotes::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
