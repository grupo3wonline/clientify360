<?php

declare(strict_types=1);

namespace App\Filament\Resources\Deals\Schemas;

use App\Models\Client;
use App\Models\PipelineStage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Oportunidade')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->label('Título')->required()->maxLength(255)->columnSpanFull(),
                        Select::make('client_id')->label('Cliente')
                            ->options(fn (): array => Client::orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()->required(),
                        Select::make('pipeline_stage_id')->label('Etapa do funil')
                            ->options(fn (): array => PipelineStage::ordered()->pluck('name', 'id')->all())
                            ->searchable(),
                        TextInput::make('value')->label('Valor')->numeric()->prefix('R$')->default(0)->minValue(0),
                        TextInput::make('probability')->label('Probabilidade (%)')->numeric()->minValue(0)->maxValue(100)->default(50),
                        DatePicker::make('expected_close_date')->label('Previsão de fechamento')->displayFormat('d/m/Y'),
                        Textarea::make('description')->label('Descrição')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }
}
