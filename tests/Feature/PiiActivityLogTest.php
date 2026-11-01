<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Interaction;
use App\Models\LeadForm;
use App\Models\LeadFormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Auditoria (LGPD): alterações em dados sensíveis de Interaction e
 * LeadFormSubmission são registradas no activity_log.
 */
class PiiActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_interaction_changes_are_logged(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::create(['name' => 'João', 'email' => 'joao@ex.com']);

        $interaction = Interaction::create([
            'client_id' => $client->id,
            'type' => 'call',
            'channel' => 'phone',
            'subject' => 'Primeiro contato',
            'description' => 'Ligação inicial',
            'status' => 'open',
        ]);

        $interaction->update(['subject' => 'Contato atualizado']);

        $updated = Activity::where('log_name', 'interaction')->where('description', 'updated')->latest()->first();
        $this->assertNotNull($updated);
        $this->assertSame('Contato atualizado', $updated->properties['attributes']['subject']);
        $this->assertSame('Primeiro contato', $updated->properties['old']['subject']);
    }

    public function test_interaction_unaudited_field_does_not_create_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::create(['name' => 'Maria', 'email' => 'maria@ex.com']);

        $interaction = Interaction::create([
            'client_id' => $client->id,
            'type' => 'call',
            'channel' => 'phone',
            'subject' => 'Contato',
            'status' => 'open',
        ]);

        $countBefore = Activity::where('log_name', 'interaction')->count();

        // Alterar um campo não auditado (status) não deve gerar log.
        $interaction->update(['status' => 'resolved']);

        $this->assertSame($countBefore, Activity::where('log_name', 'interaction')->count());
    }

    public function test_lead_form_submission_changes_are_logged(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $form = LeadForm::create([
            'name' => 'Contato',
            'slug' => LeadForm::generateSlug('Contato'),
            'title' => 'Fale conosco',
            'fields' => [['key' => 'name', 'type' => 'text']],
        ]);

        $submission = LeadFormSubmission::create([
            'lead_form_id' => $form->id,
            'data' => ['name' => 'João'],
            'note' => 'Nota inicial',
            'ip' => '127.0.0.1',
        ]);

        $submission->update(['note' => 'Nota atualizada']);

        $updated = Activity::where('log_name', 'lead_form_submission')->where('description', 'updated')->latest()->first();
        $this->assertNotNull($updated);
        $this->assertSame('Nota atualizada', $updated->properties['attributes']['note']);
        $this->assertSame('Nota inicial', $updated->properties['old']['note']);
    }

    public function test_lead_form_submission_unaudited_field_does_not_create_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $form = LeadForm::create([
            'name' => 'Contato',
            'slug' => LeadForm::generateSlug('Contato'),
            'title' => 'Fale conosco',
            'fields' => [['key' => 'name', 'type' => 'text']],
        ]);

        $submission = LeadFormSubmission::create([
            'lead_form_id' => $form->id,
            'data' => ['name' => 'Maria'],
            'note' => 'Nota',
            'ip' => '127.0.0.1',
        ]);

        $countBefore = Activity::where('log_name', 'lead_form_submission')->count();

        // Alterar um campo não auditado (ip) não deve gerar log.
        $submission->update(['ip' => '10.0.0.1']);

        $this->assertSame($countBefore, Activity::where('log_name', 'lead_form_submission')->count());
    }
}
