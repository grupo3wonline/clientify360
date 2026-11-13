<?php

declare(strict_types=1);

/*
 * Honeypot anti-spam tests.
 *
 * The `website` field is a honeypot: it must be absent or empty.
 * Legitimate browsers leave it untouched; bots that auto-fill forms fill it.
 * Any non-empty value triggers a 422 Unprocessable Content response.
 */

it('rejects a login attempt when the honeypot field is filled', function (): void {
    $this->post('/login', [
        'username' => 'any',
        'email' => 'any@example.com',
        'password' => 'password',
        'website' => 'http://spam.example.com', // bot-filled
    ])->assertStatus(422);
});

it('rejects a registration attempt when the honeypot field is filled', function (): void {
    $this->post('/register', [
        'name' => 'Bot',
        'username' => 'botuser',
        'email' => 'bot@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'lgpd_consent' => '1',
        'website' => 'http://spam.example.com', // bot-filled
    ])->assertStatus(422);
});

it('rejects a forgot-password attempt when the honeypot field is filled', function (): void {
    $this->post('/forgot-password', [
        'email' => 'any@example.com',
        'website' => 'filled-by-bot',
    ])->assertStatus(422);
});

it('accepts a login attempt when the honeypot field is absent', function (): void {
    // No `website` key: the form is processed normally (even if credentials are wrong).
    $response = $this->post('/login', [
        'username' => 'nonexistent',
        'email' => 'nonexistent@example.com',
        'password' => 'password',
        // `website` deliberately absent
    ]);

    // Should fail auth but NOT be rejected as a honeypot hit (422).
    expect($response->status())->not->toBe(422);
});

it('accepts a login attempt when the honeypot field is explicitly empty', function (): void {
    $response = $this->post('/login', [
        'username' => 'nonexistent',
        'email' => 'nonexistent@example.com',
        'password' => 'password',
        'website' => '', // empty string: legitimate browser behavior
    ]);

    expect($response->status())->not->toBe(422);
});

it('renders the honeypot field hidden in the login form', function (): void {
    $html = (string) $this->get('/login')->assertOk()->getContent();

    // The honeypot input must be in the page but visually hidden.
    expect($html)->toContain('name="website"');
});

it('renders the honeypot field hidden in the register form', function (): void {
    $html = (string) $this->get('/register')->assertOk()->getContent();

    expect($html)->toContain('name="website"');
});
