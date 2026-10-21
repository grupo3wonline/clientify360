<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_validation_messages_in_portuguese(): void
    {
        $this->assertSame('pt_BR', app()->getLocale());

        $validator = Validator::make(
            ['email' => 'nao-e-email'],
            ['email' => ['required', 'email']],
        );

        $message = $validator->errors()->first('email');

        $this->assertStringContainsString('e-mail', $message);
        $this->assertStringContainsString('válido', $message);
    }

    public function test_it_renders_form_request_validation_errors_in_portuguese(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => 'Teste',
                'email' => 'nao-e-email',
            ]);

        $response->assertSessionHasErrors('email');

        $message = session('errors')->get('email')[0];
        $this->assertStringContainsString('válido', $message);
    }
}
