<?php

declare(strict_types=1);

/* AC-53, R-PRIV-4: privacy policy content. */

it('covers the controller, DPO, LGPD, GDPR, CCPA/CPRA and POPIA', function (): void {
    $response = $this->get(route('privacy'))->assertOk();

    $response->assertSee('Grupo 3W Online')
        ->assertSee('Encarregado pelo tratamento de dados (DPO) / Information Officer (POPIA)')
        ->assertSee('mailto:contato@3wonline.com.br', false)
        ->assertSee('LGPD (Brasil)')
        ->assertSee('GDPR (União Europeia)')
        ->assertSee('CCPA/CPRA (Califórnia)')
        ->assertSee('POPIA (África do Sul)')
        ->assertSee('Não vendemos informações pessoais.')
        ->assertSee('Information Regulator')
        ->assertSee('Última atualização: '.config('legal.updated_at'));
});

it('has the 11 sections with their anchors', function (): void {
    $html = (string) $this->get(route('privacy'))->getContent();

    $sections = [
        'quem-somos' => '1. Quem somos',
        'dados-coletados' => '2. Dados que coletamos',
        'finalidades' => '3. Finalidades e bases legais',
        'cookies' => '4. Cookies',
        'compartilhamento' => '5. Compartilhamento e transferências internacionais',
        'retencao' => '6. Retenção',
        'direitos' => '7. Seus direitos',
        'como-exercer' => '8. Como exercer seus direitos',
        'seguranca' => '9. Segurança',
        'criancas' => '10. Crianças',
        'alteracoes' => '11. Alterações',
    ];
    foreach ($sections as $id => $heading) {
        expect($html)->toContain('id="'.$id.'"')->toContain($heading);
    }
    expect($html)->toContain('id="nao-vender"');
});

it('lists the cookies, transfers and legal bases', function (): void {
    $this->get(route('privacy'))
        ->assertSee(config('session.cookie'))
        ->assertSee('XSRF-TOKEN')
        ->assertSee('c360_consent')
        ->assertSee('180 dias')
        ->assertSee('_ga')
        ->assertSee('Stripe')
        ->assertSee('cláusulas contratuais padrão')
        ->assertSee('art. 33 da LGPD')
        ->assertSee('Consentimento (I)')
        ->assertSee('Legítimo interesse (f)')
        ->assertSee('data-cookie-preferences', false)
        ->assertSee('data-do-not-sell', false);
});
