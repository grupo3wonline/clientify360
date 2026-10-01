<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadForms\Pages;

use App\Filament\Resources\LeadForms\LeadFormResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListLeadForms extends ListRecords
{
    protected static string $resource = LeadFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('novo')
                ->label('Novo formulário')
                ->icon('heroicon-m-plus')
                ->url(route('lead-forms.create')),
        ];
    }
}
