<?php

declare(strict_types=1);

use App\Models\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Personas;

/*
 * Upload validation beyond AttachmentTest (PHP renamed to .pdf, .exe, 11 MB):
 * content-sniffed MIME on both upload paths (client attachments and the
 * account logo). Nothing is stored when validation fails.
 */

beforeEach(function (): void {
    Storage::fake('media');
    $this->owner = Personas::owner();
    $this->client = Client::factory()->create(['account_id' => $this->owner->account_id, 'user_id' => $this->owner->id]);
    $this->actingAs($this->owner);
});

it('rejects attachments whose content does not match an allowed type', function (?UploadedFile $file): void {
    $this->from(route('clients.show', $this->client))
        ->post(route('clients.attachments.store', $this->client), ['file' => $file])
        ->assertSessionHasErrors('file');

    expect($this->client->fresh()->getMedia('attachments'))->toHaveCount(0);
    Storage::disk('media')->assertDirectoryEmpty('/');
})->with([
    'html renamed to txt' => fn () => realUpload('nota.txt', '<!DOCTYPE html><html><body><script>alert(1)</script></body></html>'),
    'svg with script' => fn () => realUpload('foto.png', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
    'php with a pdf extension and fake mime' => fn () => UploadedFile::fake()->createWithContent('x.pdf', '<?php echo 1;'),
    'double extension' => fn () => realUpload('contrato.pdf.php', "%PDF-1.4\n<?php echo 1; ?>"),
    'no file' => fn () => null,
]);

it('rejects unsafe or oversized logos', function (UploadedFile $file, string $message): void {
    $this->post(route('account.logo.update'), ['logo' => $file])
        ->assertSessionHasErrorsIn('accountLogo', ['logo' => $message]);

    expect($this->owner->account->fresh()->getFirstMedia('logo'))->toBeNull();
})->with([
    'svg' => [fn () => realUpload('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'), 'Envie uma imagem PNG, JPG ou WEBP de até 2 MB.'],
    'over 2 MB' => [fn () => UploadedFile::fake()->image('logo.png', 100, 100)->size(3000), 'Envie uma imagem PNG, JPG ou WEBP de até 2 MB.'],
    'over 2000 px' => [fn () => UploadedFile::fake()->image('logo.png', 2500, 100), 'A imagem deve ter no máximo 2000 × 2000 pixels.'],
]);
