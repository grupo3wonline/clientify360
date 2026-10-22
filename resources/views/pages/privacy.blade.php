@extends('layouts.public')

@section('title', 'Política de Privacidade')
@section('meta_description', 'Política de Privacidade do Clientify360. Saiba como coletamos, usamos e protegemos seus dados pessoais em conformidade com a LGPD, GDPR, CCPA e POPIA.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-600 to-primary-800 dark:from-primary-800 dark:to-gray-900 text-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Política de Privacidade</h1>
            <p class="text-lg md:text-xl text-primary-100 max-w-3xl mx-auto">
                Transparência e segurança no tratamento dos seus dados pessoais, em conformidade com a LGPD.
            </p>
        </div>
    </section>

    <!-- Privacy Content -->
    <section class="py-16 md:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 md:p-12 border border-gray-100 dark:border-gray-700">

                <!-- Last Updated -->
                <div class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-calendar-days mr-2"></i>
                        Última atualização: {{ date('d/m/Y') }}
                    </p>
                </div>

                <!-- Introduction -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-file-shield text-primary-600 dark:text-primary-400 mr-2"></i>
                        Introdução
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                        O Clientify360 está comprometido com a proteção da privacidade e dos dados pessoais de seus usuários. Esta Política de Privacidade descreve como coletamos, utilizamos, armazenamos e protegemos suas informações, em conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018).
                    </p>
                </div>

                <!-- Data Collection -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-database text-primary-600 dark:text-primary-400 mr-2"></i>
                        1. Coleta de Dados
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        Coletamos apenas os dados necessários para o funcionamento do sistema e para oferecer a melhor experiência possível. Os dados coletados incluem:
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span><strong>Dados de cadastro:</strong> nome, e-mail, nome de usuário e senha (criptografada).</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span><strong>Dados de uso:</strong> informações sobre como você utiliza o sistema, páginas acessadas e funcionalidades utilizadas.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span><strong>Dados técnicos:</strong> endereço IP, tipo de navegador, sistema operacional e dados de conexão.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span><strong>Dados de clientes cadastrados:</strong> informações que você insere no sistema sobre seus próprios clientes.</span>
                        </li>
                    </ul>
                </div>

                <!-- Data Usage -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-gears text-primary-600 dark:text-primary-400 mr-2"></i>
                        2. Uso dos Dados
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        Utilizamos seus dados pessoais para as seguintes finalidades:
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fa-solid fa-check text-secondary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Fornecer e manter o funcionamento do sistema.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-check text-secondary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Autenticar e identificar o usuário de forma segura.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-check text-secondary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Enviar comunicações importantes sobre o serviço (atualizações, segurança).</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-check text-secondary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Melhorar a experiência do usuário e aprimorar funcionalidades.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-check text-secondary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Cumprir obrigações legais e regulatórias.</span>
                        </li>
                    </ul>
                </div>

                <!-- Data Storage -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-server text-primary-600 dark:text-primary-400 mr-2"></i>
                        3. Armazenamento e Segurança
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        Seus dados são armazenados em servidores seguros com as seguintes medidas de proteção:
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fa-solid fa-lock text-primary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Criptografia de senhas utilizando algoritmos seguros (bcrypt).</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-lock text-primary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Conexão segura via HTTPS/SSL em todas as comunicações.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-lock text-primary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Isolamento completo de dados entre usuários (cada usuário acessa apenas seus dados).</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-lock text-primary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Backups regulares para prevenção de perda de dados.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-lock text-primary-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Proteção contra ataques (CSRF, XSS, SQL Injection).</span>
                        </li>
                    </ul>
                </div>

                <!-- User Rights -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-user-shield text-primary-600 dark:text-primary-400 mr-2"></i>
                        4. Seus Direitos (LGPD)
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        De acordo com a LGPD, você possui os seguintes direitos sobre seus dados pessoais:
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-eye text-primary-500 mr-2"></i>Acesso
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Solicitar acesso aos dados pessoais que mantemos sobre você.</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-pen text-primary-500 mr-2"></i>Correção
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Solicitar a correção de dados incompletos, inexatos ou desatualizados.</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-trash-can text-primary-500 mr-2"></i>Exclusão
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Solicitar a exclusão dos seus dados pessoais do nosso sistema.</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-file-arrow-down text-primary-500 mr-2"></i>Portabilidade
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Solicitar a portabilidade dos seus dados para outro serviço.</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-ban text-primary-500 mr-2"></i>Revogação
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Revogar o consentimento para o tratamento dos seus dados a qualquer momento.</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-circle-info text-primary-500 mr-2"></i>Informação
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Ser informado sobre com quem seus dados são compartilhados.</p>
                        </div>
                    </div>
                </div>

                <!-- International Privacy Laws -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-globe text-primary-600 dark:text-primary-400 mr-2"></i>
                        Conformidade com Leis Internacionais de Privacidade
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        Além da LGPD, o Clientify360 respeita os princípios das principais legislações internacionais de proteção de dados. Caso você resida em uma dessas jurisdições, garantimos os direitos correspondentes:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-flag text-primary-500 mr-2"></i>GDPR — União Europeia
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Regulamento Geral sobre a Proteção de Dados (UE 2016/679). Você tem direito de acesso, retificação, exclusão ("direito ao esquecimento"), portabilidade, restrição e oposição ao tratamento, além de base legal explícita para cada finalidade.</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-flag text-primary-500 mr-2"></i>CCPA — Califórnia (EUA)
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">California Consumer Privacy Act. Você pode saber quais dados coletamos, solicitar sua exclusão e optar por não ter seus dados vendidos. O Clientify360 <strong>não vende</strong> dados pessoais.</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-flag text-primary-500 mr-2"></i>POPIA — África do Sul
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Protection of Personal Information Act. Tratamos os dados de forma lícita, mínima e para finalidade específica, garantindo acesso, correção e exclusão mediante solicitação.</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-2">
                                <i class="fa-solid fa-flag text-primary-500 mr-2"></i>LGPD — Brasil
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Lei Geral de Proteção de Dados (Lei nº 13.709/2018), base principal desta política, com todos os direitos descritos na seção anterior.</p>
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mt-4">
                        Para exercer qualquer um desses direitos, independentemente da sua localização, utilize os canais de contato indicados no final desta política.
                    </p>
                </div>

                <!-- Cookies -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-cookie-bite text-primary-600 dark:text-primary-400 mr-2"></i>
                        5. Cookies
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        Utilizamos cookies essenciais para o funcionamento do sistema:
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span><strong>Cookies de sessão:</strong> necessários para manter sua autenticação ativa durante o uso do sistema.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span><strong>Cookies de preferência:</strong> armazenam suas preferências (como tema claro/escuro) para melhorar a experiência.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span><strong>Cookies de segurança:</strong> utilizados para proteção contra ataques CSRF e validação de formulários.</span>
                        </li>
                    </ul>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mt-4">
                        Não utilizamos cookies de publicidade ou de rastreamento de terceiros para fins comerciais. O serviço de segurança Google reCAPTCHA pode definir cookies próprios, utilizados exclusivamente para distinguir acessos humanos de automatizados (consulte a seção a seguir).
                    </p>
                </div>

                <!-- reCAPTCHA / Anti-bot -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-robot text-primary-600 dark:text-primary-400 mr-2"></i>
                        6. Proteção contra Robôs (Google reCAPTCHA)
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        Para proteger nossos formulários (cadastro, login, recuperação de senha e captação de contatos) contra uso automatizado, spam e fraudes, utilizamos o serviço <strong>Google reCAPTCHA v3</strong>. Esse serviço opera de forma invisível, sem exigir interação adicional do usuário.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span>O reCAPTCHA coleta dados de hardware e software (como endereço IP e interações com a página) e os envia ao Google para análise de comportamento.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span>Esses dados são tratados pelo Google como operador independente, sendo utilizados exclusivamente para distinguir acessos humanos de acessos automatizados.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span>O uso do serviço está sujeito à
                                <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="text-primary-600 dark:text-primary-400 underline hover:no-underline">Política de Privacidade</a>
                                e aos
                                <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer" class="text-primary-600 dark:text-primary-400 underline hover:no-underline">Termos de Serviço</a>
                                do Google.</span>
                        </li>
                    </ul>
                </div>

                <!-- Data Sharing -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-share-nodes text-primary-600 dark:text-primary-400 mr-2"></i>
                        7. Compartilhamento de Dados
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        O Clientify360 <strong>não vende, aluga ou compartilha</strong> seus dados pessoais com terceiros para fins comerciais. Seus dados poderão ser compartilhados apenas nas seguintes situações:
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span>Quando exigido por lei ou ordem judicial.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span>Para proteger os direitos, propriedade ou segurança do Clientify360 e seus usuários.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span>Com prestadores de serviço essenciais (hospedagem, e-mail), sob contratos de confidencialidade.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-circle-dot text-primary-500 text-xs mt-2 mr-3 flex-shrink-0"></i>
                            <span>Com o Google reCAPTCHA, para fins de segurança e prevenção a fraudes, conforme descrito na seção anterior.</span>
                        </li>
                    </ul>
                </div>

                <!-- Contact for Data Requests -->
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-envelope-open-text text-primary-600 dark:text-primary-400 mr-2"></i>
                        8. Contato para Solicitações de Dados
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                        Para exercer qualquer um dos seus direitos previstos na LGPD, ou para esclarecer dúvidas sobre esta política, entre em contato conosco:
                    </p>
                    <div class="bg-primary-50 dark:bg-gray-700 rounded-xl p-6 border border-primary-200 dark:border-gray-600">
                        <p class="text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fa-solid fa-envelope text-primary-600 dark:text-primary-400 mr-2"></i>
                            <strong>E-mail:</strong> privacidade@clientify360.com.br
                        </p>
                        <p class="text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fa-solid fa-clock text-primary-600 dark:text-primary-400 mr-2"></i>
                            <strong>Prazo de resposta:</strong> até 15 dias úteis
                        </p>
                        <p class="text-gray-700 dark:text-gray-300">
                            <i class="fa-solid fa-file-lines text-primary-600 dark:text-primary-400 mr-2"></i>
                            <strong>Formulário:</strong> disponível na página de <a href="{{ route('contact') }}" class="text-primary-600 dark:text-primary-400 underline hover:no-underline">Contato</a>
                        </p>
                    </div>
                </div>

                <!-- Changes to Policy -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        <i class="fa-solid fa-rotate text-primary-600 dark:text-primary-400 mr-2"></i>
                        9. Alterações nesta Política
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                        Esta Política de Privacidade pode ser atualizada periodicamente para refletir mudanças em nossas práticas ou em requisitos legais. Recomendamos que você revise esta página regularmente. Alterações significativas serão comunicadas por e-mail ou por aviso no sistema.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 md:py-20 bg-gradient-to-br from-primary-700 to-primary-900 dark:from-primary-900 dark:to-gray-900 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <i class="fa-solid fa-shield-halved text-4xl mb-6 text-primary-200"></i>
            <h2 class="text-2xl md:text-3xl font-bold mb-4">Precisa de um sistema seguro e em conformidade com a LGPD?</h2>
            <p class="text-primary-100 text-lg mb-8 max-w-2xl mx-auto leading-relaxed">
                O <strong>Grupo 3W Online</strong> desenvolve sites e sistemas personalizados sob demanda, com foco em segurança, privacidade e conformidade legal. Proteja seu negócio com tecnologia de qualidade.
            </p>
            <a href="https://www.3wonline.com.br" target="_blank" rel="noopener noreferrer" class="inline-flex items-center bg-white text-primary-700 font-bold px-8 py-4 rounded-xl hover:bg-primary-50 transition shadow-lg text-lg">
                <i class="fa-solid fa-globe mr-2"></i> Conheça o Grupo 3W Online
            </a>
        </div>
    </section>
@endsection
