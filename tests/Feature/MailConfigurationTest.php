<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

/** Builds the real SMTP transport from the given config, without opening any connection. */
function mailBuildSmtpTransport(array $smtp): EsmtpTransport
{
    config(['mail.default' => 'smtp', 'mail.mailers.smtp' => array_merge(config('mail.mailers.smtp'), $smtp)]);
    app('mail.manager')->forgetMailers();

    $transport = app('mail.manager')->mailer('smtp')->getSymfonyTransport();
    assert($transport instanceof EsmtpTransport);

    return $transport;
}

// ------------------------------------------------ HostGator SMTP transport

it('builds an implicit-TLS transport for the HostGator setup (port 465, smtps)', function (): void {
    $transport = mailBuildSmtpTransport([
        'host' => 'sh-pro32.hostgator.com.br', 'port' => 465, 'scheme' => 'smtps',
        'username' => 'nao-responder@clientify360.com.br', 'password' => 'segredo',
    ]);

    // Symfony prints "smtps://host" and omits the port when it is the scheme's default (465),
    // so read host and port from the stream, the real connection settings.
    expect($transport->getStream()->isTLS())->toBeTrue()
        ->and($transport->getStream()->getHost())->toBe('sh-pro32.hostgator.com.br')
        ->and($transport->getStream()->getPort())->toBe(465)
        ->and((string) $transport)->toStartWith('smtps://')
        ->and($transport->getUsername())->toBe('nao-responder@clientify360.com.br');
});

it('picks smtps by itself on port 465 when MAIL_SCHEME is left empty', function (): void {
    $transport = mailBuildSmtpTransport([
        'host' => 'sh-pro32.hostgator.com.br', 'port' => 465, 'scheme' => null,
        'username' => 'a@b.com.br', 'password' => 'x',
    ]);

    expect($transport->getStream()->isTLS())->toBeTrue();
});

it('does not use implicit TLS on the submission port 587', function (): void {
    $transport = mailBuildSmtpTransport([
        'host' => 'sh-pro32.hostgator.com.br', 'port' => 587, 'scheme' => null,
        'username' => 'a@b.com.br', 'password' => 'x',
    ]);

    expect($transport->getStream()->isTLS())->toBeFalse();
});

// ------------------------------------------- the three e-mails of the flow

it('queues the activation e-mail on registration', function (): void {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Maria Silva', 'username' => 'maria_mail', 'email' => 'maria.mail@example.com', 'lgpd_consent' => '1',
        'password' => 'Senha12345', 'password_confirmation' => 'Senha12345',
    ])->assertRedirect(route('verification.notice'));

    Notification::assertSentTo(User::where('email', 'maria.mail@example.com')->first(), VerifyEmailNotification::class);
});

it('queues the password-reset e-mail', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('delivers all three e-mails with the configured sender and a link back to this app', function (): void {
    config(['mail.from.address' => 'nao-responder@clientify360.com.br', 'mail.from.name' => 'Clientify360', 'app.url' => 'https://clientify360.com.br']);
    Mail::fake();

    $user = User::factory()->unverified()->create();
    $verify = (new VerifyEmailNotification)->toMail($user);
    $reset = (new ResetPasswordNotification('tok123'))->toMail($user);

    expect($verify->actionUrl)->toContain('/verify-email/')->toContain('signature=')
        ->and($reset->actionUrl)->toContain('/reset-password/tok123')->toContain(rawurlencode($user->email))
        ->and($verify->subject)->toBe('Confirme seu endereço de e-mail')
        ->and($reset->subject)->toBe('Redefinição de senha');
});

it('sends the activation e-mail again on request, only to the signed-in unverified user', function (): void {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');

    Notification::assertSentToTimes($user, VerifyEmailNotification::class, 1);
});

// ---------------------------------------------------------- mail:test command

it('mail:test sends one message through the configured mailer', function (): void {
    Mail::fake();

    $this->artisan('mail:test', ['to' => 'destino@example.com'])
        ->expectsOutputToContain('Mensagem entregue ao servidor SMTP para destino@example.com')
        ->assertExitCode(0);

    Mail::assertSentCount(0); // raw mail is not a Mailable
});

it('mail:test rejects an invalid address without sending', function (): void {
    $this->artisan('mail:test', ['to' => 'isto-nao-e-email'])
        ->expectsOutputToContain('Endereço inválido')
        ->assertExitCode(1);
});

it('mail:test reports the real SMTP error instead of failing silently', function (): void {
    config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.scheme' => 'smtp']);
    app('mail.manager')->forgetMailers();

    $this->artisan('mail:test', ['to' => 'destino@example.com'])
        ->expectsOutputToContain('Falha no envio')
        ->assertExitCode(1);
});

it('mail:test never prints the SMTP password', function (): void {
    config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1,
        'mail.mailers.smtp.scheme' => 'smtp', 'mail.mailers.smtp.password' => 'SEGREDO-NAO-VAZAR']);
    app('mail.manager')->forgetMailers();

    $this->artisan('mail:test', ['to' => 'destino@example.com'])->doesntExpectOutputToContain('SEGREDO-NAO-VAZAR');
});

it('mail:test warns when the mailer cannot deliver anything', function (): void {
    config(['mail.default' => 'log']);

    $this->artisan('mail:test', ['to' => 'destino@example.com'])
        ->expectsOutputToContain('nada será entregue de verdade');
});
