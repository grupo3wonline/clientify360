<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDataExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_the_authenticated_users_personal_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Titular Teste',
            'email' => 'titular@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('profile.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');

        $content = $response->streamedContent();
        $this->assertStringContainsString('titular@example.com', $content);
        $this->assertStringContainsString('Titular Teste', $content);

        // Nunca expõe credenciais nem chaves de IA.
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('ai_gemini_key', $content);
    }

    public function test_it_does_not_include_other_users_data_in_the_export(): void
    {
        $user = User::factory()->create(['email' => 'eu@example.com']);
        User::factory()->create(['email' => 'outro@example.com', 'name' => 'Outro Usuario']);

        $response = $this->actingAs($user)->get(route('profile.export'));

        $content = $response->assertOk()->streamedContent();

        $this->assertStringContainsString('eu@example.com', $content);
        $this->assertStringNotContainsString('outro@example.com', $content);
        $this->assertStringNotContainsString('Outro Usuario', $content);
    }

    public function test_it_requires_authentication_to_export_personal_data(): void
    {
        $this->get(route('profile.export'))->assertRedirect(route('login'));
    }
}
