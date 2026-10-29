<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;

/*
 * Cross-account IDOR on every model-bound web route: account A acting on
 * account B's record always gets 404 and B's record is left untouched.
 * The attacker is a Profissional owner, so no plan gate can answer first.
 */

const IDOR_WEB_ROUTES = [
    // [route name, route parameter, HTTP method]
    ['clients.show', 'client', 'get'],
    ['clients.edit', 'client', 'get'],
    ['clients.update', 'client', 'put'],
    ['clients.destroy', 'client', 'delete'],
    ['clients.attachments.store', 'client', 'post'],
    ['ai.analyze', 'client', 'post'],
    ['appointments.show', 'appointment', 'get'],
    ['appointments.edit', 'appointment', 'get'],
    ['appointments.update', 'appointment', 'put'],
    ['appointments.destroy', 'appointment', 'delete'],
    ['appointments.confirm', 'appointment', 'patch'],
    ['appointments.done', 'appointment', 'patch'],
    ['appointments.cancel', 'appointment', 'patch'],
    ['service-orders.show', 'service_order', 'get'],
    ['service-orders.edit', 'service_order', 'get'],
    ['service-orders.update', 'service_order', 'put'],
    ['service-orders.destroy', 'service_order', 'delete'],
    ['service-orders.start', 'service_order', 'patch'],
    ['service-orders.complete', 'service_order', 'patch'],
    ['service-orders.cancel', 'service_order', 'patch'],
    ['service-orders.billing', 'service_order', 'patch'],
    ['service-orders.pdf', 'service_order', 'get'],
    ['purchases.show', 'purchase', 'get'],
    ['purchases.confirm', 'purchase', 'patch'],
    ['products.show', 'product', 'get'],
    ['products.edit', 'product', 'get'],
    ['products.update', 'product', 'put'],
    ['products.destroy', 'product', 'delete'],
    ['products.movement', 'product', 'post'],
    ['quotes.show', 'quote', 'get'],
    ['quotes.edit', 'quote', 'get'],
    ['quotes.update', 'quote', 'put'],
    ['quotes.destroy', 'quote', 'delete'],
    ['quotes.print', 'quote', 'get'],
    ['quotes.pdf', 'quote', 'get'],
    ['quotes.send', 'quote', 'post'],
    ['quotes.approve', 'quote', 'post'],
    ['quotes.reject', 'quote', 'post'],
    ['quotes.duplicate', 'quote', 'post'],
    ['deals.show', 'deal', 'get'],
    ['deals.update', 'deal', 'put'],
    ['deals.destroy', 'deal', 'delete'],
    ['deals.move', 'deal', 'patch'],
    ['deals.win', 'deal', 'post'],
    ['deals.lose', 'deal', 'post'],
    ['deals.reopen', 'deal', 'post'],
    ['stages.update', 'stage', 'put'],
    ['stages.destroy', 'stage', 'delete'],
    ['activities.destroy', 'activity', 'delete'],
    ['tags.update', 'tag', 'put'],
    ['tags.destroy', 'tag', 'delete'],
    ['reminders.complete', 'reminder', 'patch'],
    ['reminders.destroy', 'reminder', 'delete'],
    ['interactions.updateStatus', 'interaction', 'patch'],
    ['interactions.destroy', 'interaction', 'delete'],
    ['finance.update', 'entry', 'put'],
    ['finance.settle', 'entry', 'post'],
    ['finance.reopen', 'entry', 'post'],
    ['finance.cancel', 'entry', 'post'],
    ['finance.destroy', 'entry', 'delete'],
    ['finance.categories.destroy', 'category', 'delete'],
    ['lead-forms.show', 'lead_form', 'get'],
    ['lead-forms.edit', 'lead_form', 'get'],
    ['lead-forms.update', 'lead_form', 'put'],
    ['lead-forms.destroy', 'lead_form', 'delete'],
    ['lead-forms.toggle', 'lead_form', 'post'],
    ['team.updateRole', 'member', 'patch'],
    ['team.removeMember', 'member', 'delete'],
    ['team.revokeInvite', 'invitation', 'delete'],
];

dataset('model-bound web routes', IDOR_WEB_ROUTES);

it('answers 404 when an account touches another account record', function (string $route, string $parameter, string $method): void {
    $victim = Personas::owner('professional');
    $record = AccountRecords::make($parameter, $victim); // created as guest
    $snapshot = $record->fresh()?->getAttributes();
    $attacker = Personas::owner('professional');

    $payload = $parameter === 'member' ? ['role' => User::ROLE_MANAGER] : [];
    $this->actingAs($attacker)
        ->{$method.'Json'}(route($route, [$parameter => $record->getKey()]), $payload)
        ->assertNotFound();

    auth()->logout();
    expect($record->fresh()?->getAttributes())->toBe($snapshot, "{$route} changed the other account record");
})->with('model-bound web routes');

it('covers every model-bound web route in the IDOR dataset', function (): void {
    $covered = collect(IDOR_WEB_ROUTES)->map(fn (array $row): string => $row[0]);
    $exempt = [
        // Second parameter scoped by the parent client; covered in AttachmentTest.
        'clients.attachments.show', 'clients.attachments.destroy',
        // {tokenId}/{id} are not model bindings; scoped to the user in the controller (ApiTokenManagementTest, NotificationController).
        'profile.tokens.destroy', 'notifications.markRead',
        // Public routes, looked up by public slug / token.
        'lead-forms.public', 'lead-forms.submit', 'password.reset', 'verification.verify',
    ];

    $bound = collect(app('router')->getRoutes()->getRoutes())
        ->filter(fn ($route): bool => in_array('web', $route->gatherMiddleware(), true)
            && $route->getName() !== null
            && !str_starts_with($route->getName(), 'filament.')
            && !str_starts_with($route->getName(), 'l5-swagger.')
            && !str_starts_with($route->getName(), 'livewire.') // vendor, signed upload previews
            && $route->parameterNames() !== [])
        ->map(fn ($route): string => (string) $route->getName())
        ->reject(fn (string $name): bool => in_array($name, $exempt, true))
        ->values();

    expect($bound->diff($covered)->values()->all())->toBe([]);
});
