<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ContactMessageDTO;
use Illuminate\Support\Facades\Log;

final class ContactService
{
    /**
     * Frequently asked questions used on the public landing page.
     * Also consumed by the FAQPage structured data (AEO/GEO).
     *
     * @return array<int, array{question: string, answer: string}>
     */
    public function faqs(): array
    {
        return [
            [
                'question' => 'O que é o Clientify360?',
                'answer' => 'O Clientify360 é um CRM com inteligência artificial integrada para gestão de clientes. Ele oferece score, análises automáticas de perfil, controle de atendimentos, lembretes e relatórios, tudo em uma interface simples e responsiva.',
            ],
            [
                'question' => 'Quanto custa o Clientify360?',
                'answer' => 'O plano Profissional está em promoção: de R$ 79,90 por R$ 29,90 por mês. Há também um plano Básico gratuito com as funcionalidades essenciais de cadastro de clientes, agenda e lembretes.',
            ],
            [
                'question' => 'Preciso pagar para usar a inteligência artificial?',
                'answer' => 'Os recursos de IA fazem parte do plano Profissional. Cada usuário utiliza suas próprias credenciais dos provedores (Google Gemini, Groq ou OpenRouter), sendo que o OpenRouter Gemma 3n possui camada gratuita.',
            ],
            [
                'question' => 'Meus dados ficam seguros e isolados?',
                'answer' => 'Sim. Cada usuário acessa somente seus próprios dados, com isolamento por escopo global, criptografia e sessões seguras. A plataforma é totalmente aderente à LGPD.',
            ],
            [
                'question' => 'Posso cancelar quando quiser?',
                'answer' => 'Sim. A assinatura pode ser cancelada a qualquer momento pela área de faturamento, sem multa ou fidelidade. Você mantém o acesso até o fim do período já pago.',
            ],
            [
                'question' => 'O Clientify360 funciona no celular?',
                'answer' => 'Sim. A interface foi construída com abordagem mobile first e é 100% responsiva, funcionando bem em celulares, tablets e computadores.',
            ],
        ];
    }

    /**
     * Handle an incoming contact message from the public landing page.
     * Returns false when the honeypot indicates a bot submission.
     */
    public function handle(ContactMessageDTO $dto): bool
    {
        if ($dto->isSpam()) {
            return false;
        }

        Log::channel('single')->info('Contact message received', [
            'name' => $dto->name,
            'email' => $dto->email,
            'subject' => $dto->subject,
        ]);

        return true;
    }
}
