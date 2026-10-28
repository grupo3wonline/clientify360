<?php

declare(strict_types=1);

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Str;

it('does not expose the scaffolded /api/user route without a token', function (): void {
    $this->getJson('/api/user')->assertNotFound();
});

it('does not expose the scaffolded /api/user route with a valid token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->getJson('/api/user')->assertNotFound();
});

it('stores Sanctum tokens with UUID keys', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->accessToken;

    expect($token)->toBeInstanceOf(PersonalAccessToken::class)
        ->and(Str::isUuid((string) $token->getKey()))->toBeTrue()
        ->and($token->tokenable_id)->toBe($user->id);
});

it('never serializes the AI provider keys', function (): void {
    $user = User::factory()->create([
        'ai_gemini_key' => 'x',
        'ai_groq_key' => 'y',
        'ai_openrouter_key' => 'z',
    ]);

    expect($user->toArray())
        ->not->toHaveKey('ai_gemini_key')
        ->not->toHaveKey('ai_groq_key')
        ->not->toHaveKey('ai_openrouter_key');
});
