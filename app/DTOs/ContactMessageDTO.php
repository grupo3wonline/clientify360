<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ContactMessageDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $subject,
        public string $message,
        public ?string $honeypot,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            subject: $data['subject'] ?? null,
            message: $data['message'],
            honeypot: $data['website'] ?? null,
        );
    }

    /**
     * A filled honeypot field indicates an automated (bot) submission.
     */
    public function isSpam(): bool
    {
        return filled($this->honeypot);
    }
}
