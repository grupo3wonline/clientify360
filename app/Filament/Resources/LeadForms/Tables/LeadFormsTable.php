<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadForms\Tables;

use App\Models\LeadForm;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadFormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable()->weight('medium'),
                TextColumn::make('slug')->label('Link público')
                    ->formatStateUsing(fn (string $state): string => '/f/'.$state)
                    ->copyable()->toggleable(),
                TextColumn::make('submissions_count')->label('Envios')->badge()->sortable(),
                IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->recordActions([
                Action::make('editar')
                    ->label('Editar')
                    ->icon('heroicon-m-pencil-square')
                    ->authorize('update')
                    ->url(fn (LeadForm $record): string => route('lead-forms.edit', $record)),
                Action::make('publico')
                    ->label('Ver público')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (LeadForm $record): string => route('lead-forms.public', $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nenhum formulário de captação')
            ->emptyStateDescription('Crie formulários pelo módulo de Captação de leads.');
    }
}
