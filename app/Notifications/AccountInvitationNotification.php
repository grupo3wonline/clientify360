<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\AccountInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Queued invitation e-mail sent on demand to the invited address. */
final class AccountInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly string $token,
        public readonly string $accountName,
        public readonly string $role,
        public readonly ?string $inviterName = null,
    ) {}

    public static function for(AccountInvitation $invitation, string $accountName, ?User $inviter): self
    {
        return new self($invitation->token, $accountName, $invitation->role, $inviter?->name);
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300];
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $inviter = $this->inviterName ?: 'Um administrador';
        $role = User::ROLE_LABELS[$this->role] ?? $this->role;

        return (new MailMessage)
            ->subject('Convite para a conta '.$this->accountName.' no Clientify360')
            ->greeting('Olá!')
            ->line("{$inviter} convidou você para a conta {$this->accountName} no Clientify360 como {$role}.")
            ->action('Aceitar convite', route('register', ['invite' => $this->token]))
            ->line('O convite expira em 7 dias.')
            ->line('Se você não esperava este convite, ignore este e-mail.');
    }
}
