<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;

/**
 * LGPD personal-data export payload for a single data subject (the logged-in user).
 *
 * Scope is strictly the user's own identity, privacy consent and account
 * membership. It never includes other users' data, the whole tenant,
 * credentials or AI keys.
 */
final readonly class PersonalDataExportDTO
{
    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public string $exportedAt,
        public string $id,
        public string $name,
        public ?string $username,
        public string $email,
        public ?string $emailVerifiedAt,
        public ?string $createdAt,
        public ?string $updatedAt,
        public bool $lgpdConsent,
        public ?string $lgpdConsentAt,
        public ?string $termsAcceptedAt,
        public ?string $termsVersion,
        public ?string $accountId,
        public array $roles,
    ) {}

    /**
     * Build the DTO from a user and the roles resolved under the account team context.
     *
     * @param  array<int, string>  $roles
     */
    public static function fromUser(User $user, array $roles): self
    {
        return new self(
            exportedAt: now()->toIso8601String(),
            id: $user->id,
            name: $user->name,
            username: $user->username,
            email: $user->email,
            emailVerifiedAt: optional($user->email_verified_at)->toIso8601String(),
            createdAt: optional($user->created_at)->toIso8601String(),
            updatedAt: optional($user->updated_at)->toIso8601String(),
            lgpdConsent: (bool) $user->lgpd_consent,
            lgpdConsentAt: optional($user->lgpd_consent_at)->toIso8601String(),
            termsAcceptedAt: optional($user->terms_accepted_at)->toIso8601String(),
            termsVersion: $user->terms_version,
            accountId: $user->account_id,
            roles: $roles,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'exported_at' => $this->exportedAt,
            'subject' => [
                'id' => $this->id,
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'email_verified_at' => $this->emailVerifiedAt,
                'created_at' => $this->createdAt,
                'updated_at' => $this->updatedAt,
            ],
            'privacy' => [
                'lgpd_consent' => $this->lgpdConsent,
                'lgpd_consent_at' => $this->lgpdConsentAt,
                'terms_accepted_at' => $this->termsAcceptedAt,
                'terms_version' => $this->termsVersion,
            ],
            'account_membership' => [
                'account_id' => $this->accountId,
                'roles' => $this->roles,
            ],
        ];
    }
}
