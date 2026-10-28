<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Media;
use App\Services\SubscriptionService;
use App\Support\AccountCache;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\Personas;

beforeEach(function (): void {
    Storage::fake('media');

    $this->other = Personas::owner();
    $this->foreignClient = Client::factory()->create(['account_id' => $this->other->account_id]);

    $this->owner = Personas::owner();
    $this->client = Client::factory()->create(['account_id' => $this->owner->account_id]);
});

function pdfUpload(string $name = 'contrato.pdf', int $kb = 1024): UploadedFile
{
    return realUpload($name, "%PDF-1.4\n".str_repeat('0', max(0, $kb * 1024 - 9)));
}

it('uploads a 1 MB PDF to the private disk', function (): void {
    $this->actingAs($this->owner)
        ->post(route('clients.attachments.store', $this->client), ['file' => pdfUpload()])
        ->assertRedirect(route('clients.show', $this->client).'#anexos')
        ->assertSessionHas('success');

    $media = $this->client->fresh()->getFirstMedia('attachments');

    expect($media)->not->toBeNull()
        ->and($media->disk)->toBe('media')
        ->and($media->mime_type)->toBe('application/pdf');
    Storage::disk('media')->assertExists($media->getPathRelativeToRoot());
});

it('rejects unsafe or oversized files with pt-BR messages', function (UploadedFile $file, string $message): void {
    $this->actingAs($this->owner)
        ->from(route('clients.show', $this->client))
        ->post(route('clients.attachments.store', $this->client), ['file' => $file])
        ->assertSessionHasErrors(['file' => $message]);

    expect($this->client->fresh()->getMedia('attachments'))->toHaveCount(0);
})->with([
    'php renamed to pdf' => [fn () => realUpload('nota.pdf', "<?php echo 'x'; ?>"), 'Formato não permitido. Envie PDF, imagem, texto, Word ou Excel.'],
    'executable' => [fn () => realUpload('setup.exe', "MZ\x90\x00".str_repeat("\x00", 256)), 'Formato não permitido. Envie PDF, imagem, texto, Word ou Excel.'],
    '11 MB pdf' => [fn () => pdfUpload('grande.pdf', 11 * 1024), 'O arquivo deve ter no máximo 10 MB.'],
]);

it('returns 404 for another account client or media', function (): void {
    $foreignMedia = $this->foreignClient->addMedia(pdfUpload())->toMediaCollection('attachments');

    $this->actingAs($this->owner);

    $this->post(route('clients.attachments.store', $this->foreignClient), ['file' => pdfUpload()])->assertNotFound();
    $this->get(route('clients.attachments.show', [$this->foreignClient, $foreignMedia->id]))->assertNotFound();
    // Own client, foreign media id: scoped by parent.
    $this->get(route('clients.attachments.show', [$this->client, $foreignMedia->id]))->assertNotFound();
    $this->delete(route('clients.attachments.destroy', [$this->client, $foreignMedia->id]))->assertNotFound();

    expect(Media::whereKey($foreignMedia->id)->exists())->toBeTrue();
});

it('downloads as an attachment and writes a privacy log', function (): void {
    $media = $this->client->addMedia(pdfUpload())->toMediaCollection('attachments');

    $response = $this->actingAs($this->owner)->get(route('clients.attachments.show', [$this->client, $media->id]))->assertOk();

    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
    expect(Activity::where('log_name', 'privacy')->where('event', 'attachment.downloaded')->where('causer_id', $this->owner->id)->exists())->toBeTrue();
});

it('returns 404 and logs when the file is missing on disk', function (): void {
    $media = $this->client->addMedia(pdfUpload())->toMediaCollection('attachments');
    Storage::disk('media')->delete($media->getPathRelativeToRoot());

    $this->actingAs($this->owner)->get(route('clients.attachments.show', [$this->client, $media->id]))->assertNotFound();
});

it('deletes an own attachment', function (): void {
    $media = $this->client->addMedia(pdfUpload())->toMediaCollection('attachments');

    $this->actingAs($this->owner)->delete(route('clients.attachments.destroy', [$this->client, $media->id]))->assertRedirect();

    expect(Media::whereKey($media->id)->exists())->toBeFalse();
});

it('sends the limit redirect back to the Anexos card', function (): void {
    $plans = Mockery::mock(SubscriptionService::class, [new AccountCache])->makePartial();
    $plans->shouldReceive('canCreate')->with(Mockery::any(), 'client_attachments')->andReturnFalse();
    $this->app->instance(SubscriptionService::class, $plans);

    $this->actingAs($this->owner)
        ->from(route('clients.show', $this->client))
        ->post(route('clients.attachments.store', $this->client), ['file' => pdfUpload()])
        ->assertRedirect(route('clients.show', $this->client).'#anexos')
        ->assertSessionHas('error');
});

it('shows the Anexos card with the plan usage', function (): void {
    $this->actingAs($this->owner)->get(route('clients.show', $this->client))
        ->assertOk()
        ->assertSee('id="anexos"', false)
        ->assertSee('Seu plano permite 10 anexos (0 usados).');
});
