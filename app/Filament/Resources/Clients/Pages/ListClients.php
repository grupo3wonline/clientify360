<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->disabled(fn (): bool => !$this->planAllowsCreation())
                ->tooltip(fn (): ?string => $this->planAllowsCreation() ? null : 'Limite do plano atingido'),
        ];
    }

    private function planAllowsCreation(): bool
    {
        $user = auth()->user();

        return $user instanceof User && app(SubscriptionService::class)->canCreate($user, 'clients');
    }
}
