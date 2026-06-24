<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\ClientDTO;
use App\Models\Client;
use App\Services\ClientService;

final class CreateClientAction
{
    public function __construct(
        private readonly ClientService $service
    ) {}

    public function execute(ClientDTO $dto): Client
    {
        return $this->service->create($dto);
    }
}
