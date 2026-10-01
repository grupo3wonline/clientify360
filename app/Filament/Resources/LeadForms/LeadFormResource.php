<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadForms;

use App\Filament\Resources\LeadForms\Pages\ListLeadForms;
use App\Filament\Resources\LeadForms\Tables\LeadFormsTable;
use App\Models\LeadForm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Captação de leads no painel = lista/consulta. A criação/edição (com o
 * construtor de campos) permanece no fluxo Blade dedicado.
 */
class LeadFormResource extends Resource
{
    protected static ?string $model = LeadForm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Comercial';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'formulário';

    protected static ?string $pluralModelLabel = 'formulários de captação';

    protected static ?string $navigationLabel = 'Captação de leads';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return LeadFormsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeadForms::route('/'),
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
