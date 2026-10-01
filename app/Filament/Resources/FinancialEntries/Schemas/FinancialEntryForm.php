<?php

declare(strict_types=1);

namespace App\Filament\Resources\FinancialEntries\Schemas;

use App\Models\FinancialEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FinancialEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lançamento')
                    ->columns(2)
                    ->schema([
                        Select::make('direction')
                            ->label('Tipo')
                            ->options(['receivable' => 'A receber', 'payable' => 'A pagar'])
                            ->required(),
                        TextInput::make('description')->label('Descrição')->required()->maxLength(255),
                        TextInput::make('amount')->label('Valor')->numeric()->prefix('R$')->required()->minValue(0),
                        DatePicker::make('due_date')->label('Vencimento')->required()->displayFormat('d/m/Y'),
                        Select::make('payment_method')
                            ->label('Forma de pagamento')
                            ->options(FinancialEntry::PAYMENT_METHODS)
                            ->placeholder('—'),
                        TextInput::make('counterparty')->label('Cliente/Fornecedor')->maxLength(255),
                    ]),
            ]);
    }
}
