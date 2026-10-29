<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Product;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;

/* AC-56: user content is escaped in Blade pages, the Filament panel and the public lead form. */

const XSS_PAYLOAD = '<script>alert("xss")</script><img src=x onerror=alert(1)>';

function assertEscaped(string $html, string $page): void
{
    test()->assertStringNotContainsString('<script>alert("xss")</script>', $html, "{$page}: raw script tag");
    test()->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html, "{$page}: raw event handler");
    test()->assertStringContainsString('&lt;script&gt;', $html, "{$page}: payload not rendered at all");
}

it('escapes user content on Blade and panel pages', function (): void {
    $owner = Personas::owner('professional');
    $base = ['account_id' => $owner->account_id, 'user_id' => $owner->id];
    $client = Client::factory()->create($base + ['name' => XSS_PAYLOAD, 'notes' => XSS_PAYLOAD]);
    $product = Product::factory()->forAccount($owner->account_id)->create(['user_id' => $owner->id, 'name' => XSS_PAYLOAD]);
    $quote = AccountRecords::make('quote', $owner);
    $quote->update(['title' => XSS_PAYLOAD, 'client_id' => $client->id]);
    $deal = AccountRecords::make('deal', $owner);
    $deal->update(['title' => XSS_PAYLOAD]);

    $this->actingAs($owner);

    $pages = [
        route('clients.index'), route('clients.show', $client), route('clients.edit', $client),
        route('products.index'), route('products.show', $product),
        route('quotes.index'), route('quotes.show', $quote), route('quotes.print', $quote),
        route('deals.list'), route('deals.show', $deal),
        '/app/clients', "/app/clients/{$client->id}/edit", '/app/products', '/app/quotes', '/app/deals',
    ];

    foreach ($pages as $url) {
        assertEscaped((string) $this->get($url)->assertOk()->getContent(), $url);
    }
});

it('escapes the lead form texts on the public page', function (): void {
    $owner = Personas::owner();
    $form = AccountRecords::make('lead_form', $owner);
    $form->update(['title' => XSS_PAYLOAD, 'description' => XSS_PAYLOAD]);

    assertEscaped((string) $this->get(route('lead-forms.public', $form->slug))->assertOk()->getContent(), '/f');
});

it('stores the payload as typed (escaping happens on output)', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);

    $this->post(route('clients.store'), ['name' => XSS_PAYLOAD, 'person_type' => 'individual'])->assertRedirect();

    expect(Client::query()->value('name'))->toBe(XSS_PAYLOAD);
});
