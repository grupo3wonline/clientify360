<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Quote;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\Personas;

/* R-PRIV-5: access to personal data is logged under the "privacy" log name. */

beforeEach(function (): void {
    $this->user = Personas::owner('professional');
    $this->client = Client::factory()->create(['account_id' => $this->user->account_id]);
});

function privacyEvent(string $event, string $causerId): ?Activity
{
    return Activity::where('log_name', 'privacy')->where('event', $event)->where('causer_id', $causerId)->first();
}

it('logs the personal data export (data.exported)', function (): void {
    $this->actingAs($this->user)->get(route('profile.export'))->assertOk()->streamedContent();

    $log = privacyEvent('data.exported', $this->user->id);
    expect($log)->not->toBeNull()
        ->and($log->subject_id)->toBe($this->user->id);
});

it('logs the client export (clients.exported)', function (): void {
    $this->actingAs($this->user)->get(route('export.clients'))->assertOk();

    expect(privacyEvent('clients.exported', $this->user->id))->not->toBeNull();
});

it('logs attachment downloads (attachment.downloaded)', function (): void {
    Storage::fake('media');
    $media = $this->client->addMedia(realUpload('contrato.pdf', "%PDF-1.4\n".str_repeat('0', 2048)))->toMediaCollection('attachments');

    $this->actingAs($this->user)->get(route('clients.attachments.show', [$this->client, $media->id]))->assertOk();

    expect(privacyEvent('attachment.downloaded', $this->user->id))->not->toBeNull();
});

it('logs PDF generation (pdf.generated)', function (): void {
    $quote = Quote::factory()->forClient($this->client)->create();

    $this->actingAs($this->user)->get(route('quotes.pdf', $quote))->assertOk();

    expect(privacyEvent('pdf.generated', $this->user->id))->not->toBeNull();
});

it('logs consent changes of authenticated users (consent.updated)', function (): void {
    $this->actingAs($this->user)
        ->postJson(route('consent.store'), ['analytics' => true, 'marketing' => false, 'source' => 'preferences'])
        ->assertNoContent();

    expect(privacyEvent('consent.updated', $this->user->id))->not->toBeNull();
});

it('does not log anonymous consent in the activity log', function (): void {
    $this->postJson(route('consent.store'), ['analytics' => true, 'marketing' => true, 'source' => 'banner'])->assertNoContent();

    expect(Activity::where('log_name', 'privacy')->where('event', 'consent.updated')->exists())->toBeFalse();
});

it('shows the "Privacidade e dados" card with every in-app right on the profile', function (): void {
    $this->actingAs($this->user)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Privacidade e dados')
        ->assertSee(route('profile.export'))
        ->assertSee('href="#dados-do-perfil"', false)
        ->assertSee('id="dados-do-perfil"', false)
        ->assertSee('Corrigir meus dados')
        ->assertSee('<button type="button" data-cookie-preferences', false)
        ->assertSee('href="#excluir-conta"', false)
        ->assertSee('id="excluir-conta"', false)
        ->assertSee('mailto:contato@3wonline.com.br', false)
        ->assertSee('Você pode retirar o consentimento de cookies opcionais a qualquer momento.');
});
