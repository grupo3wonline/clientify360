<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

it('queues both auth notifications', function (string $class): void {
    expect(is_subclass_of($class, ShouldQueue::class))->toBeTrue();
})->with([VerifyEmailNotification::class, ResetPasswordNotification::class]);

it('sends the queued verification e-mail in pt-BR', function (): void {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo($user, VerifyEmailNotification::class, function (VerifyEmailNotification $notification) use ($user): bool {
        $mail = $notification->toMail($user);

        return $mail->subject === 'Confirme seu endereço de e-mail'
            && in_array('Clique no botão abaixo para confirmar seu e-mail.', $mail->introLines, true)
            && $mail->actionText === 'Confirme seu endereço de e-mail';
    });
});

it('sends the queued password reset e-mail in pt-BR', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user): bool {
        $mail = $notification->toMail($user);

        return $mail->subject === 'Redefinição de senha'
            && $mail->actionText === 'Redefinir senha'
            && str_contains(implode(' ', $mail->outroLines), 'Este link de redefinição de senha expira em');
    });
});

it('renders the mail layout strings in pt-BR', function (): void {
    $user = User::factory()->create();
    $html = (string) (new ResetPasswordNotification('token'))->toMail($user)->render();

    expect($html)->toContain('Olá!')
        ->and($html)->toContain('Atenciosamente,')
        ->and($html)->toContain('copie e cole a URL abaixo');
});
