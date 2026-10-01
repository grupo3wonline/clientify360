<?php

declare(strict_types=1);

namespace App\Filament\Resources\Interactions\Schemas;

use App\Models\Client;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InteractionForm
{
    public const TYPES = [
        'support' => 'Suporte',
        'sales' => 'Vendas',
        'follow_up' => 'Follow-up',
        'complaint' => 'Reclamação',
        'other' => 'Outro',
    ];

    public const CHANNELS = [
        'phone' => 'Telefone',
        'email' => 'E-mail',
        'whatsapp' => 'WhatsApp',
        'meeting' => 'Reunião',
        'other' => 'Outro',
    ];

    public const STATUSES = [
        'open' => 'Aberto',
        'in_progress' => 'Em andamento',
        'resolved' => 'Resolvido',
        'closed' => 'Fechado',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')->label('Cliente')
                    ->options(fn (): array => Client::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->required(),
                TextInput::make('subject')->label('Assunto')->required()->maxLength(255),
                Select::make('type')->label('Tipo')->options(self::TYPES)->default('support')->required(),
                Select::make('channel')->label('Canal')->options(self::CHANNELS)->default('phone')->required(),
                Select::make('status')->label('Situação')->options(self::STATUSES)->default('open')->required(),
                Textarea::make('description')->label('Descrição')->rows(3)->columnSpanFull(),
            ])
            ->columns(2);
    }
}
