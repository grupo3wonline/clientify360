<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ClientDTO
{
    public function __construct(
        public ?string $id,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $document,
        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $zipCode,
        public ?string $notes,
        public ?string $source = null,
        /** @var array<int, string>|null null = não mexer nas tags */
        public ?array $tagIds = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            document: $data['document'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            zipCode: $data['zip_code'] ?? null,
            notes: $data['notes'] ?? null,
            source: $data['source'] ?? null,
            tagIds: array_key_exists('tag_ids', $data) ? array_values((array) $data['tag_ids']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document' => $this->document,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zipCode,
            'notes' => $this->notes,
            'source' => $this->source,
        ];
    }
}
