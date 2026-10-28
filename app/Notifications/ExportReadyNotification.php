<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly string $url,
    ) {}

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
        return (new MailMessage)
            ->subject('Sua planilha de clientes está pronta')
            ->greeting('Olá!')
            ->line('A planilha de clientes que você pediu no Clientify360 está pronta para download.')
            ->action('Baixar planilha', $this->url)
            ->line('O link vale por 24 horas e só funciona com você conectado à sua conta.');
    }
}
