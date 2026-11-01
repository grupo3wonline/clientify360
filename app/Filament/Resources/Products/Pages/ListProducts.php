<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

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

        return $user instanceof User && app(SubscriptionService::class)->canCreate($user, 'products');
    }
}
