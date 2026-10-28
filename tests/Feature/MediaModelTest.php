<?php

declare(strict_types=1);

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

it('uses Laravel HasUuids', function (): void {
    expect(class_uses_recursive(Media::class))->toHaveKey(HasUuids::class)
        ->and(config('media-library.media_model'))->toBe(Media::class);
});

it('fills both id and uuid with valid UUIDs', function (): void {
    $owner = User::factory()->create();

    $media = Media::create([
        'model_type' => $owner->getMorphClass(),
        'model_id' => $owner->id,
        'collection_name' => 'default',
        'name' => 'contrato',
        'file_name' => 'contrato.pdf',
        'mime_type' => 'application/pdf',
        'disk' => 'media',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect(Str::isUuid((string) $media->getKey()))->toBeTrue()
        ->and(Str::isUuid((string) $media->uuid))->toBeTrue()
        ->and($media->getKey())->not->toBe($media->uuid);
});
