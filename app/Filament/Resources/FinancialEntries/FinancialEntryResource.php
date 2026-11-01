<?php

declare(strict_types=1);

namespace App\Filament\Resources\FinancialEntries;

use App\Filament\Resources\FinancialEntries\Pages\CreateFinancialEntry;
use App\Filament\Resources\FinancialEntries\Pages\EditFinancialEntry;
use App\Filament\Resources\FinancialEntries\Pages\ListFinancialEntries;
use App\Filament\Resources\FinancialEntries\Schemas\FinancialEntryForm;
use App\Filament\Resources\FinancialEntries\Tables\FinancialEntriesTable;
use App\Models\FinancialEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FinancialEntryResource extends Resource
{
    protected static ?string $model = FinancialEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Financeiro';

    protected static ?string $modelLabel = 'lançamento';

    protected static ?string $pluralModelLabel = 'contas a pagar/receber';

    protected static ?string $navigationLabel = 'Contas a pagar/receber';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return FinancialEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialEntries::route('/'),
            'create' => CreateFinancialEntry::route('/create'),
            'edit' => EditFinancialEntry::route('/{record}/edit'),
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
