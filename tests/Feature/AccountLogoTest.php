<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Personas;

beforeEach(fn () => Storage::fake('media'));

it('lets the owner upload, preview and remove the account logo', function (): void {
    $owner = Personas::owner();

    $this->actingAs($owner)->post(route('account.logo.update'), ['logo' => UploadedFile::fake()->image('logo.png', 300, 100)])
        ->assertRedirect(route('profile.edit').'#logotipo');

    expect($owner->account->fresh()->getFirstMedia('logo'))->not->toBeNull();
    $this->get(route('account.logo.show'))->assertOk()->assertHeader('Content-Type', 'image/png');
    $this->get(route('profile.edit'))->assertOk()->assertSee('Logotipo da conta');

    $this->delete(route('account.logo.destroy'))->assertRedirect();
    expect($owner->account->fresh()->getFirstMedia('logo'))->toBeNull();
    $this->get(route('account.logo.show'))->assertNotFound();
});

it('rejects a non-image logo in pt-BR', function (): void {
    $this->actingAs(Personas::owner())
        ->post(route('account.logo.update'), ['logo' => realUpload('logo.png', '<?php echo 1; ?>')])
        ->assertSessionHasErrorsIn('accountLogo', ['logo' => 'Envie uma imagem PNG, JPG ou WEBP de até 2 MB.']);
});

it('forbids collaborators from changing the logo', function (): void {
    $owner = Personas::owner();
    $collaborator = Personas::collaborator($owner->account);

    $this->actingAs($collaborator)->post(route('account.logo.update'), ['logo' => UploadedFile::fake()->image('logo.png')])->assertForbidden();
    $this->actingAs($collaborator)->get(route('profile.edit'))->assertOk()->assertDontSee('Logotipo da conta');
});

it('removes the logo when the owner deletes the account', function (): void {
    $owner = Personas::owner();
    $owner->account->addMedia(UploadedFile::fake()->image('logo.png'))->toMediaCollection('logo');

    $this->actingAs($owner)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');

    expect($owner->account->fresh()->getMedia('logo'))->toHaveCount(0);
});
