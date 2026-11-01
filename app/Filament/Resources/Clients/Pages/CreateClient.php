<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Concerns\EnforcesPlanLimit;
use App\Filament\Resources\Clients\ClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    use EnforcesPlanLimit;

    protected static string $resource = ClientResource::class;

    protected static function planResource(): string
    {
        return 'clients';
    }
}
