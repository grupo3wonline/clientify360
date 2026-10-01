<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clients\Schemas;

use App\Models\Client;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do cliente')
                    ->description('Informações principais de contato e identificação.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('document')
                            ->label('CPF/CNPJ')
                            ->maxLength(20),
                        Select::make('source')
                            ->label('Origem do lead')
                            ->options(Client::SOURCES)
                            ->searchable()
                            ->placeholder('Como chegou até você'),
                    ]),
                Section::make('Endereço')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('address')->label('Endereço')->maxLength(255)->columnSpanFull(),
                        TextInput::make('city')->label('Cidade')->maxLength(120),
                        TextInput::make('state')->label('UF')->maxLength(2),
                        TextInput::make('zip_code')->label('CEP')->maxLength(10),
                    ]),
                Section::make('Observações')
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')->label('Anotações')->rows(4)->columnSpanFull(),
                    ]),
            ]);
    }
}
