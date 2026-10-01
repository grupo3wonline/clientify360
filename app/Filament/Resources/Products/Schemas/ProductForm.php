<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Tipo')
                            ->options(['product' => 'Produto', 'service' => 'Serviço'])
                            ->default('product')
                            ->required()
                            ->live(),
                        TextInput::make('name')->label('Nome')->required()->maxLength(255),
                        TextInput::make('sku')->label('Código (SKU)')->maxLength(60),
                        Select::make('unit')->label('Unidade')->options(Product::UNITS)->default('un'),
                        Textarea::make('description')->label('Descrição')->rows(2)->columnSpanFull(),
                    ]),
                Section::make('Preço e custo')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')->label('Preço de venda')->numeric()->prefix('R$')->required()->minValue(0),
                        TextInput::make('promo_price')->label('Preço promocional')->numeric()->prefix('R$')->minValue(0)
                            ->helperText('Tem prioridade sobre o preço de venda quando preenchido.'),
                        TextInput::make('cost')->label('Custo')->numeric()->prefix('R$')->minValue(0),
                        TextInput::make('ncm')->label('NCM')->maxLength(20),
                        TextInput::make('supplier')->label('Fornecedor')->maxLength(255),
                    ]),
                Section::make('Estoque')
                    ->columns(3)
                    ->schema([
                        Toggle::make('track_stock')->label('Controlar estoque')->live(),
                        TextInput::make('stock')->label('Saldo atual')->numeric()->default(0)
                            ->visible(fn ($get): bool => (bool) $get('track_stock'))
                            ->helperText('Prefira registrar entradas/saídas pelas movimentações.'),
                        TextInput::make('min_stock')->label('Estoque mínimo')->numeric()->default(0)
                            ->visible(fn ($get): bool => (bool) $get('track_stock')),
                    ]),
                Toggle::make('is_active')->label('Ativo')->default(true),
            ]);
    }
}
