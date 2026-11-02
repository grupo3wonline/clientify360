<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends one message synchronously through the configured transport, so a wrong SMTP
 * setup surfaces here (with the real error) instead of silently failing in the queue.
 */
final class MailTestCommand extends Command
{
    protected $signature = 'mail:test {to : Endereço que receberá a mensagem de teste}';

    protected $description = 'Send a test e-mail through the configured SMTP (no queue) to validate registration, activation and password-reset delivery';

    public function handle(): int
    {
        $to = (string) $this->argument('to');

        if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            $this->error("Endereço inválido: {$to}");

            return self::FAILURE;
        }

        $mailer = (string) config('mail.default');

        // Never print the password: only what is needed to diagnose the setup.
        $this->line('Mailer: '.$mailer);

        if ($mailer === 'smtp') {
            $this->line(sprintf(
                'SMTP: %s:%s (%s) como %s',
                config('mail.mailers.smtp.host'),
                config('mail.mailers.smtp.port'),
                config('mail.mailers.smtp.scheme') ?: 'automático',
                config('mail.mailers.smtp.username') ?: '(sem usuário)',
            ));
        }

        $this->line('Remetente: '.config('mail.from.address'));

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn("MAIL_MAILER={$mailer}: nada será entregue de verdade. Use \"smtp\" em produção.");
        }

        try {
            Mail::raw(
                'Teste de envio do '.config('app.name').". Se você recebeu esta mensagem, o e-mail de cadastro, ativação e recuperação de senha está funcionando.\n\nEnviado em ".now()->format('d/m/Y H:i:s').'.',
                fn ($message) => $message->to($to)->subject('['.config('app.name').'] Teste de e-mail'),
            );
        } catch (Throwable $e) {
            $this->error('Falha no envio: '.$e->getMessage());
            $this->line('Dicas: confira MAIL_HOST (use o servidor da hospedagem), MAIL_PORT=465 com MAIL_SCHEME=smtps, usuário/senha da caixa e se a porta 465 está liberada.');

            return self::FAILURE;
        }

        $this->info("Mensagem entregue ao servidor SMTP para {$to}. Confira a caixa de entrada e o spam.");

        return self::SUCCESS;
    }
}
