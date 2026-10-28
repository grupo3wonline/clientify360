<?php

declare(strict_types=1);

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\FinancialEntries\FinancialEntryResource;
use App\Filament\Resources\Interactions\InteractionResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Reminders\Pages\ListReminders;
use App\Filament\Resources\Reminders\ReminderResource;
use App\Filament\Resources\Tags\TagResource;
use App\Models\Client;
use App\Models\Deal;
use App\Models\FinancialEntry;
use App\Models\Interaction;
use App\Models\Product;
use App\Models\Reminder;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\Personas;

/**
 * Every Filament resource with an edit page: account A opening account B's
 * record gets 404 (the `owned` scope hides it from route binding).
 *
 * @return array<string, array{0: class-string, 1: Closure(string): Model}>
 */
dataset('editable resources', fn (): array => [
    'clients' => [ClientResource::class, fn (string $account) => Client::factory()->create(['account_id' => $account])],
    'deals' => [DealResource::class, fn (string $account) => Deal::factory()->forAccount($account)->create([
        'client_id' => Client::factory()->create(['account_id' => $account])->id,
    ])],
    'financial entries' => [FinancialEntryResource::class, fn (string $account) => FinancialEntry::factory()->forAccount($account)->create()],
    'interactions' => [InteractionResource::class, fn (string $account) => Interaction::create([
        'account_id' => $account,
        'client_id' => Client::factory()->create(['account_id' => $account])->id,
        'type' => 'support', 'channel' => 'email', 'subject' => 'Chamado B', 'status' => 'open',
    ])],
    'products' => [ProductResource::class, fn (string $account) => Product::factory()->forAccount($account)->create()],
    'reminders' => [ReminderResource::class, fn (string $account) => Reminder::create([
        'account_id' => $account, 'title' => 'Lembrete B', 'remind_at' => now()->addDay(),
    ])],
    'tags' => [TagResource::class, fn (string $account) => Tag::create(['account_id' => $account, 'name' => 'Tag B', 'color' => '#ff0000'])],
]);

it('returns 404 when opening another account record in the panel', function (string $resource, Closure $make): void {
    $b = Personas::owner();
    $foreign = $make($b->account_id); // created as guest, before actingAs()
    $a = Personas::owner();

    $this->actingAs($a);
    Filament::setCurrentPanel('app');

    $this->get($resource::getUrl('edit', ['record' => $foreign->getKey()]))->assertNotFound();
})->with('editable resources');

it('opens the edit page of an own-account record', function (string $resource, Closure $make): void {
    $owner = Personas::owner();
    $own = $make($owner->account_id);

    $this->actingAs($owner);
    Filament::setCurrentPanel('app');

    $this->get($resource::getUrl('edit', ['record' => $own->getKey()]))->assertOk();
})->with('editable resources');

it('hides the reminder "Concluir" action from a user without clients.manage', function (): void {
    $owner = Personas::owner();
    $reminder = Reminder::create(['account_id' => $owner->account_id, 'title' => 'R', 'remind_at' => now()->addDay()]);
    $viewer = User::factory()->withoutAccount()->create(['account_id' => $owner->account_id]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($owner->account_id);
    $viewer->givePermissionTo('clients.view');

    $this->actingAs($viewer);
    Filament::setCurrentPanel('app');

    Livewire::test(ListReminders::class)
        ->assertActionHidden(TestAction::make('concluir')->table($reminder));

    $this->actingAs($owner);

    Livewire::test(ListReminders::class)
        ->assertActionVisible(TestAction::make('concluir')->table($reminder));
});
