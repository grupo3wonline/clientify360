<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\PersonalDataExportDTO;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Builds the LGPD personal-data export for a single data subject (the logged-in user).
 *
 * Isolation: all data is read from the given user only and roles are resolved
 * under that user's account team context. No cross-tenant data is ever exposed.
 */
final class PersonalDataExportService
{
    public function __construct(
        private readonly PermissionRegistrar $permissionRegistrar
    ) {}

    /**
     * Stream the authenticated user's own personal data as a JSON download.
     */
    public function streamForUser(User $user): StreamedResponse
    {
        $dto = $this->buildDto($user);
        $json = $this->encode($dto);
        $filename = 'meus-dados_'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(
            fn () => print ($json),
            $filename,
            ['Content-Type' => 'application/json'],
        );
    }

    private function buildDto(User $user): PersonalDataExportDTO
    {
        // Resolve the user's role within the account under the correct team context.
        $this->permissionRegistrar->setPermissionsTeamId($user->account_id);

        $roles = $user->getRoleNames()->values()->all();

        return PersonalDataExportDTO::fromUser($user, $roles);
    }

    private function encode(PersonalDataExportDTO $dto): string
    {
        return (string) json_encode(
            $dto->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
