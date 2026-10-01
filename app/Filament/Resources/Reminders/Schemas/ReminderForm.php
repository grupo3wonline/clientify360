<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reminders\Schemas;

use App\Models\Client;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReminderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->label('Título')->required()->maxLength(255)->columnSpanFull()
                    ->placeholder('Ex.: Ligar para o cliente sobre a proposta')
                    ->helperText('Comece com um verbo de ação para deixar o lembrete mais claro.'),
                Select::make('client_id')->label('Cliente')
                    ->options(fn (): array => Client::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->placeholder('Sem cliente vinculado')
                    ->helperText('Opcional. Vincule o lembrete a um cliente para encontrá-lo mais facilmente depois.'),
                DateTimePicker::make('remind_at')->label('Lembrar em')->required()->seconds(false)->displayFormat('d/m/Y H:i')
                    ->helperText('Data e hora em que você quer receber este lembrete.'),
                Textarea::make('description')->label('Descrição')->rows(3)->columnSpanFull()
                    ->placeholder('Detalhe o que precisa ser feito, com contexto e próximos passos.')
                    ->helperText('Opcional. Anote informações úteis para quando o lembrete chegar.'),
                Toggle::make('is_completed')->label('Concluído')
                    ->helperText('Ative quando a tarefa deste lembrete já tiver sido realizada.'),
            ])
            ->columns(2);
    }
}
