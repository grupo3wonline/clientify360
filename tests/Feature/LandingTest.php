<?php

declare(strict_types=1);

/* AC-46, AC-47, R-LAND-1..6: unified landing, recovered content and contact rules. */

function landingHtml(): string
{
    return (string) test()->get(route('home'))->assertOk()->getContent();
}

it('has every landing section in the R-LAND-1 order', function (): void {
    $html = landingHtml();
    $ids = ['inicio', 'beneficios', 'funcionalidades', 'como-funciona', 'dicas', 'servicos', 'ferramentas', 'planos', 'depoimentos', 'faq', 'sobre', 'contato'];

    $last = -1;
    foreach ($ids as $id) {
        $pos = strpos($html, 'id="'.$id.'"');
        expect($pos)->not->toBeFalse("section #{$id} missing")
            ->and($pos > $last)->toBeTrue("section #{$id} out of order");
        $last = $pos;
    }
    expect(substr_count($html, '<h1'))->toBe(1)
        ->and($html)->toContain('id="final-cta-title"');
});

it('keeps the content of the removed Home, Sobre, Serviços, Ferramentas and Contato pages', function (): void {
    $html = landingHtml();

    foreach ([
        'Nossa Missão', 'Democratizar a gestão inteligente de clientes', 'Nossos Valores', 'Privacidade em Primeiro Lugar',
        'Inovação Acessível', 'Foco no Resultado', 'IAs Integradas', 'Nossos Serviços', 'Análise com Inteligência Artificial',
        'Score & Classificação', 'Gestão de Atendimentos', 'Notificações & Lembretes', 'Google Gemini 2.0 Flash',
        'Groq Llama 3.3 70B Versatile', 'OpenRouter Gemma 3n (Gratuito)', 'Cadastro de Clientes', 'Agenda & Lembretes',
        'Relatórios & Métricas', 'Notificações Inteligentes', 'Exportação CSV', 'Segurança & LGPD', 'Funcionalidades Completas',
        'Tudo que você precisa para crescer', 'Escolha seu plano', 'Fale Conosco', 'Horário de Atendimento', 'Oferta Especial',
        'Precisa de um site ou sistema personalizado?', 'Pronto para transformar sua gestão?', 'Não perca essa oportunidade',
        'Desbloqueie TODAS as ferramentas PRO', 'Perguntas frequentes',
    ] as $heading) {
        // Texts printed with {{ }} are HTML-escapes ("&" becomes "&amp;").
        expect(str_contains($html, $heading) || str_contains($html, e($heading)))->toBeTrue("missing: {$heading}");
    }
});

it('limits the contact section to WhatsApp and e-mail, with no form or iframe', function (): void {
    $html = landingHtml();
    $start = strpos($html, 'id="contato"');
    $end = strpos($html, '</section>', (int) $start);
    $contact = substr($html, (int) $start, $end - $start);

    expect($contact)->toContain('https://wa.me/5541996924829')
        ->toContain('mailto:contato@3wonline.com.br')
        ->toContain('(41) 99692-4829')
        ->toContain('Grupo 3W Online')
        ->toContain('sites e sistemas personalizados sob demanda')
        ->toContain('utm_content=contact')
        ->not->toContain('<form')
        ->not->toContain('<iframe');
    expect($html)->not->toContain('<iframe');
});

it('never mentions the old contact address in resources', function (): void {
    $hits = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path(), FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if ($file->isFile() && str_contains((string) file_get_contents($file->getPathname()), 'contato@clientify360.com.br')) {
            $hits[] = $file->getPathname();
        }
    }

    expect($hits)->toBe([]);
});

it('includes the five AEO questions in the FAQ', function (): void {
    $html = landingHtml();

    foreach (['O que é o Clientify360?', 'Como meus dados ficam isolados de outras empresas?', 'O sistema tem API?', 'Posso gerar orçamentos em PDF?', 'Quem desenvolve o Clientify360?'] as $question) {
        expect($html)->toContain($question);
    }
});

it('places the 3W CTA in the hero and the final section', function (): void {
    $html = landingHtml();

    expect($html)->toContain('utm_content=landing-hero')->toContain('utm_content=landing-final');
});

it('keeps the 301 redirects of the removed pages', function (string $from, string $to): void {
    $this->get($from)->assertStatus(301)->assertRedirect($to);
})->with([
    ['/sobre', '/#sobre'],
    ['/servicos', '/#servicos'],
    ['/ferramentas', '/#ferramentas'],
    ['/contato', '/#contato'],
]);

it('shows no fabricated social proof', function (): void {
    $start = strpos(landingHtml(), 'id="depoimentos"');
    $html = landingHtml();
    $section = substr($html, (int) $start, strpos($html, '</section>', (int) $start) - $start);

    expect($section)->not->toContain('fa-star')
        ->not->toContain('clientes satisfeitos')
        ->toContain('Dados isolados por conta');
});
