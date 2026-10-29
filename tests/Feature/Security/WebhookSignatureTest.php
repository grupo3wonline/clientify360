<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Laravel\Cashier\Events\WebhookReceived;

/*
 * Stripe webhook signature (Stripe-Signature: t=..., v1=HMAC-SHA256). Only a
 * correctly signed, fresh payload reaches Cashier; nothing calls Stripe.
 * The unsigned and missing-secret cases are in StripeWebhookGuardTest.
 */

const WEBHOOK_SECRET = 'whsec_test_signature';

function stripeSignature(string $payload, string $secret = WEBHOOK_SECRET, ?int $timestamp = null): string
{
    $timestamp ??= time();

    return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
}

function webhookPayload(): string
{
    return (string) json_encode([
        'id' => 'evt_test_1',
        'object' => 'event',
        'type' => 'customer.created',
        'data' => ['object' => ['id' => 'cus_unknown', 'object' => 'customer']],
    ]);
}

beforeEach(function (): void {
    config(['cashier.webhook.secret' => WEBHOOK_SECRET, 'cashier.webhook.tolerance' => 300]);
    Event::fake([WebhookReceived::class]);
});

function postWebhook(string $payload, string $signature): TestResponse
{
    return test()->call('POST', '/stripe/webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => $signature,
    ], $payload);
}

it('rejects a payload signed with another secret', function (): void {
    postWebhook(webhookPayload(), stripeSignature(webhookPayload(), 'whsec_wrong'))->assertForbidden();

    Event::assertNotDispatched(WebhookReceived::class);
});

it('rejects a tampered payload', function (): void {
    $signature = stripeSignature(webhookPayload());
    $tampered = str_replace('cus_unknown', 'cus_attacker', webhookPayload());

    postWebhook($tampered, $signature)->assertForbidden();

    Event::assertNotDispatched(WebhookReceived::class);
});

it('rejects a replayed signature outside the tolerance window', function (): void {
    postWebhook(webhookPayload(), stripeSignature(webhookPayload(), WEBHOOK_SECRET, time() - 3600))->assertForbidden();

    Event::assertNotDispatched(WebhookReceived::class);
});

it('rejects a malformed signature header', function (): void {
    postWebhook(webhookPayload(), 'v1=abc')->assertForbidden();
});

it('accepts a correctly signed fresh payload', function (): void {
    postWebhook(webhookPayload(), stripeSignature(webhookPayload()))->assertOk();

    Event::assertDispatched(WebhookReceived::class);
});
