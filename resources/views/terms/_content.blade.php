{{-- Terms of Use content, shared by the public page (terms.show) and the acceptance page (terms.accept). --}}
@php
    $legal = config('legal.controller');
    $plans = \App\Data\PlanData::catalog();
    $h = $headingClass ?? 'text-lg font-bold text-surface-900 dark:text-white mt-8 mb-2 scroll-mt-24';
    $tag = $headingTag ?? 'h2';
    $link = 'text-brand-600 dark:text-brand-400 underline underline-offset-2 hover:no-underline';
@endphp
<div class="space-y-3 text-surface-600 dark:text-surface-300 leading-relaxed">
    <p class="text-sm text-surface-500 dark:text-surface-400">Versão {{ config('legal.terms_version') }}, última atualização em {{ config('legal.updated_at') }}.</p>

    <{{ $tag }} id="aceitacao" class="{{ $h }}">1. Aceitação</{{ $tag }}>
    <p>Estes Termos de Uso regem o uso do Clientify360, oferecido pelo {{ $legal['name'] }}. Ao criar uma conta ou usar a plataforma, você declara que leu e aceita estes termos e a <a href="{{ route('privacy') }}" class="{{ $link }}">Política de Privacidade</a>. Se não concordar, não use o serviço.</p>

    <{{ $tag }} id="servico" class="{{ $h }}">2. Descrição do serviço</{{ $tag }}>
    <p>O Clientify360 é um CRM online (micro SaaS) para pequenos negócios: clientes, funil de vendas, orçamentos, ordens de serviço, agenda, estoque, compras, financeiro e captação de leads. Cada empresa tem a sua própria conta, e os dados de uma conta nunca ficam visíveis para outra.</p>

    <{{ $tag }} id="planos" class="{{ $h }}">3. Planos e cobrança</{{ $tag }}>
    <ul class="list-disc pl-5 space-y-1">
        <li><strong>{{ $plans['basic']->name }}:</strong> gratuito, com limites de uso (por exemplo, até {{ $plans['basic']->limit('clients') }} clientes).</li>
        <li><strong>{{ $plans['professional']->name }}:</strong> {{ $plans['professional']->formattedPrice() }} por mês, cobrado pela Stripe com renovação mensal automática.</li>
        <li>Você pode cancelar quando quiser em <strong>Minha assinatura</strong>. O acesso Profissional continua até o fim do período já pago e não há reembolso proporcional, salvo quando a lei exigir.</li>
        <li>Ao voltar para o Básico, nada é apagado: todos os dados continuam disponíveis para ver, editar e excluir. A criação de novos registros fica bloqueada enquanto o uso estiver acima do limite, e os recursos exclusivos (IA, exportação, PDF e API) ficam indisponíveis. Os tokens de API são mantidos, mas recusados até um novo upgrade.</li>
        <li>Preços podem mudar com aviso prévio de pelo menos 30 dias; a mudança vale a partir da renovação seguinte.</li>
    </ul>

    <{{ $tag }} id="conta" class="{{ $h }}">4. Conta e equipe</{{ $tag }}>
    <p>O login exige nome de usuário, e-mail e senha. Você é responsável por manter as credenciais em sigilo e pelas ações feitas na sua conta. O dono da conta convida colaboradores e define os papéis (dono, gestor e colaborador) e as permissões de cada um.</p>

    <{{ $tag }} id="uso-aceitavel" class="{{ $h }}">5. Uso aceitável</{{ $tag }}>
    <p>É proibido usar a plataforma para atividades ilegais, spam, envio de conteúdo malicioso, tentativa de acesso a dados de outras contas, sobrecarga intencional do serviço ou tratamento de dados pessoais sem base legal. Podemos suspender contas que violem estas regras.</p>

    <{{ $tag }} id="seus-dados" class="{{ $h }}">6. Propriedade e tratamento dos dados</{{ $tag }}>
    <p>Os dados que você cadastra sobre os seus clientes pertencem à sua conta. Para esses dados, a sua empresa é a controladora e nós atuamos como operador, tratando-os apenas para prestar o serviço. Você pode exportar e excluir os seus dados conforme a <a href="{{ route('privacy') }}#como-exercer" class="{{ $link }}">Política de Privacidade</a>.</p>

    <{{ $tag }} id="api" class="{{ $h }}">7. Uso da API</{{ $tag }}>
    <p>A API REST está disponível no plano Profissional, com tokens pessoais e permissões por token. Os tokens são de uso exclusivo da sua conta, estão sujeitos a limites de requisições e podem ser revogados a qualquer momento em <strong>Meu Perfil</strong>.</p>

    <{{ $tag }} id="responsabilidade" class="{{ $h }}">8. Disponibilidade e responsabilidade</{{ $tag }}>
    <p>Trabalhamos para manter o serviço disponível e seguro, mas ele é fornecido "no estado em que se encontra", sem garantia de funcionamento ininterrupto. Não respondemos por perdas causadas por uso inadequado, falhas de redes externas ou ações de terceiros. Faça exportações periódicas dos dados importantes.</p>

    <{{ $tag }} id="lei" class="{{ $h }}">9. Lei aplicável e foro</{{ $tag }}>
    <p>Estes termos seguem a legislação brasileira. Fica eleito o foro da comarca de Curitiba/PR, salvo quando a lei garantir ao consumidor o foro do seu domicílio.</p>

    <{{ $tag }} id="alteracoes" class="{{ $h }}">10. Alterações</{{ $tag }}>
    <p>Podemos atualizar estes termos. Quando a versão mudar, o sistema pedirá um novo aceite antes de você continuar usando a plataforma.</p>

    <{{ $tag }} id="contato" class="{{ $h }}">11. Contato</{{ $tag }}>
    <p>Dúvidas sobre estes termos: <a href="mailto:{{ $legal['email'] }}" class="{{ $link }}">{{ $legal['email'] }}</a> ou WhatsApp <a href="{{ $legal['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">{{ $legal['whatsapp'] }}</a>.</p>
</div>
