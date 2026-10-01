<?php

declare(strict_types=1);

namespace App\Filament\Resources\Interactions;

use App\Filament\Resources\Interactions\Pages\CreateInteraction;
use App\Filament\Resources\Interactions\Pages\EditInteraction;
use App\Filament\Resources\Interactions\Pages\ListInteractions;
use App\Filament\Resources\Interactions\Schemas\InteractionForm;
use App\Filament\Resources\Interactions\Tables\InteractionsTable;
use App\Models\Interaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class InteractionResource extends Resource
{
    protected static ?string $model = Interaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Principal';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'atendimento';

    protected static ?string $pluralModelLabel = 'atendimentos';

    protected static ?string $navigationLabel = 'Atendimentos';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return InteractionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InteractionsTable::configure($table);
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
            'index' => ListInteractions::route('/'),
            'create' => CreateInteraction::route('/create'),
            'edit' => EditInteraction::route('/{record}/edit'),
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
