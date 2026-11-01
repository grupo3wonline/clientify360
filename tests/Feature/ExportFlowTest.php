<?php

declare(strict_types=1);

use App\Jobs\ExportClientsXlsxJob;
use App\Models\Client;
use App\Notifications\ExportFailedNotification;
use App\Notifications\ExportReadyNotification;
use App\Support\PurgeOldExports;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\Personas;

beforeEach(function (): void {
    Storage::fake('local');
    $this->other = Personas::owner('professional');
    Client::factory()->create(['account_id' => $this->other->account_id, 'name' => 'Cliente Alheio']);
    $this->pro = Personas::owner('professional');
    Client::factory()->create(['account_id' => $this->pro->account_id, 'name' => 'Cliente Próprio']);
});

it('does not accept GET on the XLSX export', function (): void {
    $this->actingAs($this->pro)->get('/export/clients/xlsx')->assertStatus(405);
});

it('throttles the XLSX export at 3 per minute', function (): void {
    Queue::fake();
    $this->actingAs($this->pro);

    foreach (range(1, 3) as $_) {
        $this->post(route('export.clients.xlsx'))->assertRedirect();
    }

    $this->post(route('export.clients.xlsx'))->assertStatus(429);
    Queue::assertPushed(ExportClientsXlsxJob::class, 3);
});

it('stores only own-account rows under exports/{account} and e-mails a signed link', function (): void {
    Notification::fake();

    (new ExportClientsXlsxJob($this->pro->id, $this->pro->account_id))->handle();

    $files = Storage::disk('local')->allFiles('exports/'.$this->pro->account_id);
    expect($files)->toHaveCount(1)
        ->and(Storage::disk('local')->allFiles('exports/'.$this->other->account_id))->toBe([]);

    Notification::assertSentTo($this->pro, ExportReadyNotification::class, function (ExportReadyNotification $n) use (&$url): bool {
        $url = $n->url;

        return str_contains($n->url, '/export/downloads') && str_contains($n->url, 'signature=');
    });

    $this->actingAs($this->pro)->get($url)->assertOk()->assertDownload();
});

it('rejects tampered or expired links in pt-BR', function (): void {
    $path = 'exports/'.$this->pro->account_id.'/x.xlsx';
    Storage::disk('local')->put($path, 'x');
    $url = URL::temporarySignedRoute('export.download', now()->addHour(), ['path' => encrypt($path)]);

    $this->actingAs($this->pro)->get($url.'x')->assertForbidden()
        ->assertSee('Este link expirou ou é inválido. Gere uma nova exportação.');

    $this->travel(2)->hours();
    $this->get($url)->assertForbidden();
});

it('returns 404 for another account file even with a valid signature', function (): void {
    $path = 'exports/'.$this->other->account_id.'/alheio.xlsx';
    Storage::disk('local')->put($path, 'x');
    $url = URL::temporarySignedRoute('export.download', now()->addHour(), ['path' => encrypt($path)]);

    $this->actingAs($this->pro)->get($url)->assertNotFound();
});

it('notifies the user when the job fails', function (): void {
    Notification::fake();

    (new ExportClientsXlsxJob($this->pro->id, $this->pro->account_id))->failed(new RuntimeException('falhou'));

    Notification::assertSentTo($this->pro, ExportFailedNotification::class);
});

it('purges exports older than two days', function (): void {
    $disk = Storage::disk('local');
    $disk->put('exports/a/old.xlsx', 'x');
    $disk->put('exports/a/new.xlsx', 'x');
    touch($disk->path('exports/a/old.xlsx'), now()->subDays(3)->getTimestamp());

    expect((new PurgeOldExports)())->toBe(1);
    $disk->assertMissing('exports/a/old.xlsx');
    $disk->assertExists('exports/a/new.xlsx');
});

it('logs the CSV export as a privacy event', function (): void {
    $this->actingAs($this->pro)->get(route('export.clients'))->assertOk();

    expect(Activity::where('log_name', 'privacy')->where('event', 'clients.exported')->exists())->toBeTrue();
});
