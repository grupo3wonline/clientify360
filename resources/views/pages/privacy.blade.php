@extends('layouts.public')

@php
    $legal = config('legal.controller');
    $title = 'Política de Privacidade | Clientify360';
@endphp

@section('seo')
    <x-seo
        :title="$title"
        description="Como o Clientify360 trata dados pessoais: controlador, finalidades, bases legais, cookies, transferências internacionais, retenção e seus direitos pela LGPD, GDPR, CCPA/CPRA e POPIA."
        :canonical="route('privacy')"
        :breadcrumbs="[['Início', url('/')], ['Política de Privacidade', route('privacy')]]"
        :schema="[\App\Support\StructuredData::webPage('Política de Privacidade', route('privacy'))]"
    />
@endsection
@section('cta_placement', 'privacy')

@section('content')
    @php
        $toc = [
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
        $h2 = 'scroll-mt-24 text-xl md:text-2xl font-bold text-surface-900 dark:text-white mb-4';
        $h3 = 'font-semibold text-surface-900 dark:text-white mb-2';
        $p = 'text-surface-600 dark:text-surface-300 leading-relaxed';
        $link = 'text-brand-600 dark:text-brand-400 underline underline-offset-2 hover:no-underline';
        $th = 'px-3 py-2 text-left font-semibold text-surface-900 dark:text-white';
        $td = 'px-3 py-2 align-top text-surface-600 dark:text-surface-300';
    @endphp

    <section class="bg-gradient-to-br from-brand-600 to-brand-800 dark:from-brand-800 dark:to-surface-900 text-white py-14 md:py-20" aria-labelledby="privacy-title">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 id="privacy-title" class="text-3xl md:text-5xl font-bold mb-4">Política de Privacidade</h1>
            <p class="text-lg text-brand-100 max-w-3xl mx-auto">
                Como tratamos dados pessoais no Clientify360, em conformidade com a LGPD (Brasil), o GDPR (União Europeia), a CCPA/CPRA (Califórnia) e a POPIA (África do Sul).
            </p>
            <p class="mt-4 text-sm text-brand-200">
                <i class="fa-solid fa-calendar-days mr-1" aria-hidden="true"></i>
                Última atualização: {{ config('legal.updated_at') }} (versão {{ config('legal.privacy_version') }})
            </p>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <x-breadcrumb :public="true" :items="[['label' => 'Política de Privacidade']]" />

        {{-- Short answer (AEO) --}}
        <div class="c-card p-6 mb-8">
            <h2 class="{{ $h3 }} text-lg">Resumo em poucas linhas</h2>
            <p class="{{ $p }}">
                O controlador dos dados é o {{ $legal['name'] }}. Usamos seus dados para prestar o serviço, cobrar a assinatura e manter a plataforma segura.
                Não vendemos informações pessoais. Cookies de análise e de marketing só são usados com o seu consentimento.
                Você pode baixar, corrigir ou excluir seus dados no próprio sistema, em <strong>Meu Perfil</strong>.
            </p>
        </div>

        <nav aria-label="Índice da política" class="c-card p-6 mb-10">
            <h2 class="{{ $h3 }}">Nesta página</h2>
            <ol class="grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm">
                @foreach ($toc as $anchor => $label)
                    <li><a href="#{{ $anchor }}" class="{{ $link }}">{{ $label }}</a></li>
                @endforeach
            </ol>
        </nav>

        <div class="space-y-12">
            <section id="quem-somos" aria-labelledby="quem-somos-title">
                <h2 id="quem-somos-title" class="{{ $h2 }}">1. Quem somos</h2>
                <p class="{{ $p }}">
                    O Clientify360 é um CRM online mantido pelo <strong>{{ $legal['name'] }}</strong> ({{ $legal['city'] }}), que é o
                    <strong>controlador</strong> (LGPD), <em>controller</em> (GDPR), <em>business</em> (CCPA) e <em>responsible party</em> (POPIA) dos dados pessoais das contas.
                    O sistema foi desenvolvido por {{ $legal['developer'] }}.
                </p>
                <ul class="mt-4 space-y-2 {{ $p }}">
                    <li><strong>E-mail:</strong> <a href="mailto:{{ $legal['email'] }}" class="{{ $link }}">{{ $legal['email'] }}</a></li>
                    <li><strong>WhatsApp:</strong> <a href="{{ $legal['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">{{ $legal['whatsapp'] }}</a></li>
                    <li><strong>Site:</strong> <a href="{{ $legal['website'] }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">www.3wonline.com.br</a></li>
                    <li>
                        <strong>Encarregado pelo tratamento de dados (DPO) / Information Officer (POPIA):</strong>
                        <a href="mailto:{{ $legal['dpo_email'] }}" class="{{ $link }}">{{ $legal['dpo_email'] }}</a>
                    </li>
                </ul>
            </section>

            <section id="dados-coletados" aria-labelledby="dados-coletados-title">
                <h2 id="dados-coletados-title" class="{{ $h2 }}">2. Dados que coletamos</h2>
                <ul class="space-y-3 {{ $p }} list-disc pl-5">
                    <li><strong>Dados da conta:</strong> nome, nome de usuário, e-mail, senha (guardada apenas como hash), papel na equipe, data dos consentimentos e do aceite dos Termos de Uso.</li>
                    <li><strong>Dados que você cadastra sobre os seus clientes</strong> (contatos, negociações, orçamentos, anexos, atendimentos). Para esses dados, <strong>a sua empresa é a controladora</strong> e nós somos o <strong>operador</strong> (<em>processor</em>, no GDPR): tratamos esses dados apenas para prestar o serviço, conforme as suas instruções.</li>
                    <li><strong>Dados de cobrança:</strong> processados pela Stripe. Não armazenamos números de cartão; guardamos apenas o identificador do cliente na Stripe, o status da assinatura e o histórico de faturas.</li>
                    <li><strong>Dados técnicos:</strong> endereço IP, navegador e registros de acesso para segurança. No registro de consentimento de cookies, o IP é guardado apenas como <em>hash</em> criptográfico (não é possível ler o IP original).</li>
                    <li><strong>Cookies:</strong> veja a seção <a href="#cookies" class="{{ $link }}">Cookies</a>.</li>
                </ul>
                <p class="mt-4 {{ $p }}">Não coletamos dados pessoais sensíveis de forma intencional. Evite cadastrar dados sensíveis dos seus clientes sem base legal.</p>
            </section>

            <section id="finalidades" aria-labelledby="finalidades-title">
                <h2 id="finalidades-title" class="{{ $h2 }}">3. Finalidades e bases legais</h2>
                <div class="overflow-x-auto rounded-xl border border-surface-200 dark:border-surface-700">
                    <table class="min-w-full text-sm">
                        <caption class="sr-only">Finalidades do tratamento e bases legais na LGPD e no GDPR</caption>
                        <thead class="bg-surface-50 dark:bg-surface-800">
                            <tr><th scope="col" class="{{ $th }}">Finalidade</th><th scope="col" class="{{ $th }}">LGPD (art. 7º)</th><th scope="col" class="{{ $th }}">GDPR (art. 6º)</th></tr>
                        </thead>
                        <tbody class="divide-y divide-surface-200 dark:divide-surface-700">
                            <tr><td class="{{ $td }}">Criar e manter a conta, autenticar o acesso e prestar o serviço</td><td class="{{ $td }}">Execução de contrato (V)</td><td class="{{ $td }}">Contrato (b)</td></tr>
                            <tr><td class="{{ $td }}">Cobrar a assinatura do plano Profissional (Stripe)</td><td class="{{ $td }}">Execução de contrato (V)</td><td class="{{ $td }}">Contrato (b)</td></tr>
                            <tr><td class="{{ $td }}">Segurança, prevenção a fraudes e robôs (reCAPTCHA), registros de acesso</td><td class="{{ $td }}">Legítimo interesse (IX)</td><td class="{{ $td }}">Legítimo interesse (f)</td></tr>
                            <tr><td class="{{ $td }}">Guardar registros fiscais e de faturamento</td><td class="{{ $td }}">Obrigação legal (II)</td><td class="{{ $td }}">Obrigação legal (c)</td></tr>
                            <tr><td class="{{ $td }}">Estatísticas de uso (Google Analytics) e medição de campanhas (Google Tag Manager)</td><td class="{{ $td }}">Consentimento (I)</td><td class="{{ $td }}">Consentimento (a)</td></tr>
                            <tr><td class="{{ $td }}">Comunicações sobre o serviço (segurança, cobrança, mudanças nos termos)</td><td class="{{ $td }}">Execução de contrato (V)</td><td class="{{ $td }}">Contrato (b)</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="cookies" aria-labelledby="cookies-title">
                <h2 id="cookies-title" class="{{ $h2 }}">4. Cookies</h2>
                <p class="{{ $p }} mb-4">Usamos cookies <strong>necessários</strong> sempre. Cookies de <strong>análise</strong> e de <strong>marketing</strong> só são ativados depois do seu consentimento no aviso de cookies, e você pode mudar a escolha quando quiser.</p>
                <div class="overflow-x-auto rounded-xl border border-surface-200 dark:border-surface-700">
                    <table class="min-w-full text-sm">
                        <caption class="sr-only">Cookies usados pelo Clientify360</caption>
                        <thead class="bg-surface-50 dark:bg-surface-800">
                            <tr><th scope="col" class="{{ $th }}">Nome</th><th scope="col" class="{{ $th }}">Categoria</th><th scope="col" class="{{ $th }}">Finalidade</th><th scope="col" class="{{ $th }}">Duração</th></tr>
                        </thead>
                        <tbody class="divide-y divide-surface-200 dark:divide-surface-700">
                            <tr><td class="{{ $td }}"><code>{{ config('session.cookie') }}</code></td><td class="{{ $td }}">Necessários</td><td class="{{ $td }}">Mantém a sessão e o login</td><td class="{{ $td }}">{{ (int) config('session.lifetime') }} minutos</td></tr>
                            <tr><td class="{{ $td }}"><code>XSRF-TOKEN</code></td><td class="{{ $td }}">Necessários</td><td class="{{ $td }}">Proteção contra ataques CSRF nos formulários</td><td class="{{ $td }}">Sessão</td></tr>
                            <tr><td class="{{ $td }}"><code>c360_consent</code></td><td class="{{ $td }}">Necessários</td><td class="{{ $td }}">Guarda a sua escolha de cookies (sem dados pessoais)</td><td class="{{ $td }}">180 dias</td></tr>
                            <tr><td class="{{ $td }}"><code>_GRECAPTCHA</code> (Google)</td><td class="{{ $td }}">Necessários</td><td class="{{ $td }}">reCAPTCHA v3 nos formulários de login e cadastro, contra robôs</td><td class="{{ $td }}">6 meses</td></tr>
                            <tr><td class="{{ $td }}"><code>_ga</code>, <code>_ga_*</code> (Google)</td><td class="{{ $td }}">Análise (opcional)</td><td class="{{ $td }}">Estatísticas de uso do Google Analytics</td><td class="{{ $td }}">Até 2 anos</td></tr>
                            <tr><td class="{{ $td }}">Google Tag Manager / anúncios (<code>_gcl_*</code>)</td><td class="{{ $td }}">Marketing (opcional)</td><td class="{{ $td }}">Medição de campanhas e publicidade</td><td class="{{ $td }}">Até 90 dias</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 {{ $p }}">A preferência de tema claro/escuro fica no armazenamento local do navegador (não é um cookie) e não sai do seu dispositivo.</p>
                <p class="mt-4">
                    <a href="{{ route('privacy') }}#cookies" data-cookie-preferences data-no-loading class="c-btn-primary">
                        <i class="fa-solid fa-sliders" aria-hidden="true"></i> Preferências de cookies
                    </a>
                </p>
            </section>

            <section id="compartilhamento" aria-labelledby="compartilhamento-title">
                <h2 id="compartilhamento-title" class="{{ $h2 }}">5. Compartilhamento e transferências internacionais</h2>
                <p class="{{ $p }} mb-4">Compartilhamos dados apenas com os fornecedores necessários para o serviço, sob contrato, ou quando a lei exigir:</p>
                <ul class="space-y-3 {{ $p }} list-disc pl-5">
                    <li><strong>Stripe</strong> (pagamentos, Estados Unidos): processa a assinatura e o cartão. Transferência com cláusulas contratuais padrão.</li>
                    <li><strong>Google</strong> (Estados Unidos): reCAPTCHA (segurança) sempre; Google Tag Manager e Google Analytics <strong>somente com o seu consentimento</strong>.</li>
                    <li><strong>Provedor de e-mail</strong>: envio de verificação de e-mail, recuperação de senha, convites e avisos.</li>
                    <li><strong>Hospedagem</strong> (HostGator, Brasil/Estados Unidos): servidores onde o sistema e o banco de dados ficam.</li>
                    <li><strong>Provedores de IA</strong> (Google Gemini, Groq, OpenRouter): apenas se você ativar a análise com IA no plano Profissional, usando as suas próprias credenciais, e só com os dados do cliente analisado.</li>
                </ul>
                <p class="mt-4 {{ $p }}">
                    Quando há transferência internacional, adotamos as salvaguardas do art. 33 da LGPD e do capítulo V do GDPR (cláusulas contratuais padrão ou garantias equivalentes do fornecedor).
                </p>
            </section>

            <section id="retencao" aria-labelledby="retencao-title">
                <h2 id="retencao-title" class="{{ $h2 }}">6. Retenção</h2>
                <ul class="space-y-2 {{ $p }} list-disc pl-5">
                    <li><strong>Dados da conta:</strong> enquanto a conta estiver ativa. Registros fiscais e de faturamento: mais 5 anos, por obrigação legal.</li>
                    <li><strong>Registros de consentimento de cookies:</strong> 5 anos (prova do consentimento).</li>
                    <li><strong>Arquivos de exportação</strong> (CSV/Excel): apagados automaticamente após 2 dias.</li>
                    <li><strong>Registros de atividade e de acesso a dados pessoais:</strong> {{ (int) config('activitylog.delete_records_older_than_days') }} dias.</li>
                    <li><strong>Logs técnicos do servidor:</strong> até 90 dias.</li>
                </ul>
            </section>

            <section id="direitos" aria-labelledby="direitos-title">
                <h2 id="direitos-title" class="{{ $h2 }}">7. Seus direitos</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="c-card p-5">
                        <h3 class="{{ $h3 }}">LGPD (Brasil)</h3>
                        <p class="text-sm {{ $p }}">Confirmação e acesso, correção, anonimização, bloqueio ou eliminação de dados desnecessários, portabilidade, informação sobre compartilhamento, revogação do consentimento e eliminação dos dados tratados com consentimento (art. 18). Você também pode reclamar à ANPD.</p>
                    </div>
                    <div class="c-card p-5">
                        <h3 class="{{ $h3 }}">GDPR (União Europeia)</h3>
                        <p class="text-sm {{ $p }}">Acesso, retificação, apagamento ("direito ao esquecimento"), limitação, portabilidade, oposição ao tratamento baseado em legítimo interesse e retirada do consentimento a qualquer momento (arts. 15 a 21). Você pode reclamar à autoridade de proteção de dados do seu país.</p>
                    </div>
                    <div id="nao-vender" class="c-card p-5 scroll-mt-24 md:col-span-2">
                        <h3 class="{{ $h3 }}">CCPA/CPRA (Califórnia)</h3>
                        <p class="text-sm {{ $p }}">Direito de saber quais dados coletamos, de excluir, de corrigir, de optar por não vender ou compartilhar (<em>opt-out of sale/sharing</em>), de limitar o uso de informações pessoais sensíveis e de não sofrer discriminação por exercer esses direitos.</p>
                        <p class="mt-3 text-sm font-semibold text-surface-900 dark:text-white">Não vendemos informações pessoais.</p>
                        <p class="mt-1 text-sm {{ $p }}">Cookies de marketing podem ser considerados "compartilhamento" para publicidade. Você pode recusá-los a qualquer momento; também respeitamos o sinal <em>Global Privacy Control</em> (GPC) do navegador.</p>
                        <p class="mt-4">
                            <a href="{{ route('privacy') }}#nao-vender" data-do-not-sell data-no-loading class="c-btn-primary">
                                <i class="fa-solid fa-user-shield" aria-hidden="true"></i> Não vender ou compartilhar minhas informações pessoais
                            </a>
                        </p>
                    </div>
                    <div class="c-card p-5 md:col-span-2">
                        <h3 class="{{ $h3 }}">POPIA (África do Sul)</h3>
                        <p class="text-sm {{ $p }}">Acesso, correção e exclusão dos seus dados, e o direito de se opor ao tratamento. O nosso Information Officer pode ser contatado em <a href="mailto:{{ $legal['dpo_email'] }}" class="{{ $link }}">{{ $legal['dpo_email'] }}</a>. Você também pode apresentar uma reclamação ao Information Regulator da África do Sul.</p>
                    </div>
                </div>
            </section>

            <section id="como-exercer" aria-labelledby="como-exercer-title">
                <h2 id="como-exercer-title" class="{{ $h2 }}">8. Como exercer seus direitos</h2>
                <p class="{{ $p }} mb-4">A maior parte dos pedidos pode ser feita na hora, dentro do sistema, em <strong>Meu Perfil</strong>:</p>
                <ul class="space-y-2 {{ $p }} list-disc pl-5">
                    <li><strong>Acesso e portabilidade:</strong> "Baixar meus dados" gera um arquivo JSON com os seus dados pessoais.</li>
                    <li><strong>Correção:</strong> edite nome, usuário e e-mail em "Informações do perfil".</li>
                    <li><strong>Exclusão:</strong> "Excluir conta" encerra o seu acesso na hora e os seus dados deixam de ficar disponíveis no sistema. Para a eliminação definitiva dos registros, peça por e-mail; guardamos apenas o que a lei obriga (veja <a href="#retencao" class="{{ $link }}">Retenção</a>).</li>
                    <li><strong>Consentimento de cookies:</strong> "Preferências de cookies", no rodapé de todas as páginas.</li>
                </ul>
                <p class="mt-4 {{ $p }}">
                    Para outros pedidos, escreva para <a href="mailto:{{ $legal['dpo_email'] }}" class="{{ $link }}">{{ $legal['dpo_email'] }}</a> a partir do e-mail cadastrado na conta.
                    Podemos pedir uma confirmação de identidade antes de atender. Prazos de resposta: até 15 dias (LGPD), 30 dias (GDPR e POPIA) e 45 dias (CCPA).
                </p>
            </section>

            <section id="seguranca" aria-labelledby="seguranca-title">
                <h2 id="seguranca-title" class="{{ $h2 }}">9. Segurança</h2>
                <p class="{{ $p }}">
                    Senhas com hash, HTTPS, dados isolados por conta, permissões por papel (dono, gestor e colaborador), proteção contra CSRF, XSS e injeção de SQL,
                    limite de tentativas de login, chaves de IA criptografadas e registro de acessos a dados pessoais (exportações, anexos e PDFs).
                    Se identificarmos um incidente que possa causar risco ou dano relevante, avisaremos você e as autoridades nos prazos legais.
                </p>
            </section>

            <section id="criancas" aria-labelledby="criancas-title">
                <h2 id="criancas-title" class="{{ $h2 }}">10. Crianças</h2>
                <p class="{{ $p }}">O Clientify360 é um serviço para empresas e profissionais. Não é destinado a menores de 18 anos e não coletamos, de forma intencional, dados de crianças.</p>
            </section>

            <section id="alteracoes" aria-labelledby="alteracoes-title">
                <h2 id="alteracoes-title" class="{{ $h2 }}">11. Alterações</h2>
                <p class="{{ $p }}">
                    Podemos atualizar esta política. Mudanças relevantes serão avisadas no sistema ou por e-mail, e o aviso de cookies volta a aparecer quando a versão mudar.
                    Leia também os <a href="{{ route('terms.show') }}" class="{{ $link }}">Termos de Uso</a>.
                </p>
                <p class="mt-3 text-sm text-surface-500 dark:text-surface-400">Última atualização: {{ config('legal.updated_at') }} (versão {{ config('legal.privacy_version') }}).</p>
            </section>
        </div>
    </div>
@endsection
