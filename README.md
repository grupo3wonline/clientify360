# Clientify360

Sistema de gestão de clientes no formato **Micro SaaS** desenvolvido com Laravel 12.

## Stack Tecnológica

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Banco de Dados | MySQL 5.7+ (compatível com MySQL 8.4 LTS) |
| Frontend | Alpine.js + Tailwind CSS + HTML5 + CSS3 |
| Autenticação | Laravel Breeze |
| Alertas | SweetAlert2 |
| Ícones | Font Awesome 6 |
| Padrão CSS | Mobile First |

## Decisões de stack

Cada tecnologia da lista pedida foi avaliada pelo ganho real para este micro SaaS hospedado na HostGator (hospedagem compartilhada, PHP 8.2, sem Node/Chrome no servidor). Os pacotes novos foram instalados respeitando `config.platform.php = 8.2.33` do Composer.

| Tecnologia | Decisão | Onde/por quê |
|---|---|---|
| Laravel 12 | Adotado (12.70) | Base da aplicação. |
| MySQL 8.4 LTS | Compatível; servidor atual é **5.7.44-48** | Versão conferida com `php artisan db:show` no banco do `.env` (HostGator). O código não usa CTEs, window functions nem `JSON_TABLE`, então roda em 5.7 e em 8.4. Recomendação: atualizar o servidor para 8.4 LTS quando o plano de hospedagem permitir. Testes usam SQLite em memória. |
| Laravel Sanctum | Adotado (4.3) | Tokens da API REST versionada `/api/v1`, com abilities e expiração por token, isolados por conta. Tabela `personal_access_tokens` com chave UUID. |
| Laravel API Resources | Adotado (nativo) | Respostas JSON da API v1. |
| OpenAPI/Swagger (L5 Swagger) | Adotado (darkaonline/l5-swagger 11.2) | Documentação da API v1; geração manual (`L5_SWAGGER_GENERATE_ALWAYS=false`) e acesso protegido. |
| Livewire 3 | **Livewire 4** | O Filament 5 exige Livewire 4; usar Livewire 3 obrigaria rebaixar o Filament. |
| FilamentPHP | Adotado (5.10) | Painel autenticado em `/app`. |
| Alpine.js | Adotado | Interatividade das páginas Blade (tema, tooltips, contagens). |
| Tailwind CSS | Adotado (3.4) | Estilos mobile first, build via Vite. |
| TanStack Table | Não adotado | As tabelas do Filament já fazem ordenação, filtros e paginação no servidor. |
| Zod | Não adotado | Validação no servidor com Form Requests, mais validação HTML5/Alpine no navegador. |
| Axios | Não adotado | Livewire e `fetch` do navegador cobrem as poucas chamadas assíncronas. O `resources/js/bootstrap.js` vazio foi removido. |
| Laravel Storage | Adotado | Disco privado `media` (`storage/app/private/media`) para anexos, sem exposição em `public/storage`. |
| Spatie Permission | Adotado (6.25) | ACL por conta (papéis Dono, Gestor, Colaborador). |
| Spatie Media Library | Adotado (11.23) | Anexos de clientes e logotipo da conta, no disco privado `media`. Tabela `media` com chave UUID. |
| Spatie Activitylog | Adotado (4.12) | Trilha de auditoria (LGPD). |
| Laravel Queue | Adotado (driver `database`) | E-mails de autenticação e exportações em fila. Tabelas `jobs`/`failed_jobs` já existem. |
| Laravel Cache | Adotado (driver `database`) | Cache de consultas pesadas por conta. |
| Laravel Scout | Adotado (10.25, driver `database`) | Busca global de clientes e produtos sem serviço externo. |
| Browsershot | Não adotado | Exige Node e Chrome, indisponíveis na hospedagem compartilhada. PDFs usam **barryvdh/laravel-dompdf 3.1**. |
| Laravel Excel (Maatwebsite) | Adotado (3.1) | Exportação XLSX. |
| Laravel Data | Adotado (spatie/laravel-data 4.23) | Apenas para DTOs novos nas fronteiras de serviço/API; os DTOs existentes continuam como estão. |
| Laravel Breeze | Adotado (2.4) | Cadastro, login (usuário + e-mail + senha), verificação de e-mail e redefinição de senha. |
| Pest / PHPUnit | Adotado (Pest 3.8 / PHPUnit 11.5) | Testes automatizados; testes de carga com pestphp/pest-plugin-stressless 3.1 (k6 v0.47) fora do `composer test`. |
| Laravel Pint | Adotado | Padronização de código (`composer lint:test`). |
| PHPStan / Larastan | Adotado (Larastan 3.12, nível 5) | `phpVersion: 80200`, para acusar sintaxe/funções de PHP 8.3+ que quebrariam na HostGator. |
| Rector | Adotado (2.6, só dry-run) | Conjuntos `php82` / `UP_TO_PHP_82`. |

### Login do painel Filament

O painel `/app` não tem tela de login própria. Visitantes são redirecionados para o login do Breeze (`/login`), que exige **nome de usuário + e-mail + senha** do mesmo usuário, com limite de tentativas, reCAPTCHA v3 e honeypot. Assim existe uma única implementação da regra. Usuários com e-mail não verificado são enviados para `/verify-email`, e o "Sair" do painel volta para a página inicial.

### Agendador e filas na HostGator (cron)

Os e-mails de verificação e de redefinição de senha são enviados pela fila (`QUEUE_CONNECTION=database`). Sem o cron, nenhum desses e-mails sai. No cPanel, crie uma tarefa cron a cada minuto usando o binário do PHP 8.2 (MultiPHP), não `/usr/local/bin/php`, que segue a versão padrão do servidor:

```bash
* * * * * cd /home/USUARIO/clientify360 && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> storage/logs/cron.log 2>&1
```

Confirme o caminho com `ls /opt/cpanel/` e `/opt/cpanel/ea-php82/root/usr/bin/php -v`. Mantenha a saída em `storage/logs/cron.log` até validar o funcionamento; depois troque por `/dev/null`.

O agendador (`routes/console.php`) executa `queue:work --stop-when-empty --max-time=50 --tries=3` a cada minuto: processa os jobs pendentes e encerra, adequado para hospedagem compartilhada. Se a função `proc_open` estiver desabilitada na hospedagem, o agendador não consegue executar comandos; nesse caso adicione uma segunda linha de cron que rode a fila diretamente:

```bash
* * * * * cd /home/USUARIO/clientify360 && /opt/cpanel/ea-php82/root/usr/bin/php artisan queue:work --stop-when-empty --max-time=50 --tries=3 >> storage/logs/cron.log 2>&1
```

Depois do deploy, cadastre um usuário de teste e confirme que o e-mail de verificação chega em até 2 minutos. Se não chegar, veja `storage/logs/cron.log` e `php artisan queue:failed` (`php artisan queue:retry all` reenvia os que falharam).

### Verificação de segurança em produção

`php artisan security:check` falha (código de saída 1) em produção quando: `APP_DEBUG` está ligado, `APP_KEY` está vazia, `APP_URL` não usa `https://`, `STRIPE_WEBHOOK_SECRET` está vazio, o cookie de sessão não é `secure` ou a chave secreta do reCAPTCHA está vazia. Para testar webhooks localmente use `stripe listen --forward-to http://clientify360.test/stripe/webhook` e copie o `whsec_...` exibido para `STRIPE_WEBHOOK_SECRET` no `.env`.

## Planos e assinatura (Stripe)

| Plano | Preço | Limites | Recursos exclusivos |
|---|---|---|---|
| Básico | Grátis | 10 clientes, 20 produtos/serviços, 5 orçamentos/mês, 1 formulário de leads, 10 anexos | — |
| Profissional | R$ 29,90/mês (referência ~~R$ 79,90~~) | Ilimitado (anexos: 1.000) | IA, exportação CSV/Excel, PDF, API |

A matriz fica em `config/plans.php` (`limits` e `features_enabled`). Os limites valem nas rotas Blade (middleware `plan.limit`), nas telas de criação do Filament (`EnforcesPlanLimit`) e na captação pública de leads; os recursos exclusivos usam o middleware `plan.feature:{ai|export|pdf|api}`. Quando a assinatura termina (ou um pagamento falha), a conta volta aos limites do Básico: nada é apagado, os registros continuam visíveis e editáveis, mas novos cadastros acima do limite ficam bloqueados.

O menu **Financeiro** reúne "Visão geral", "Contas a pagar/receber" (módulo `/finance`), "Minha assinatura" (`/billing`: plano, vigência, contagem regressiva, troca de plano) e "Histórico de pagamentos" (`/billing/invoices`: faturas do Stripe com PDF e recibo). As duas últimas exigem a permissão `account.billing` (Dono).

**Preço no Stripe.** `php artisan stripe:setup` cria, uma única vez, o preço mensal de R$ 29,90 (BRL, `lookup_key` `clientify360_professional_monthly_2990`) no produto do Profissional; ao rodar de novo ele só mostra o preço existente. Use `--dry-run` para ver as chamadas sem executá-las. Copie o `STRIPE_PROFESSIONAL_PRICE_ID=price_...` exibido para o `.env` e rode `php artisan config:clear`. Preços antigos não são arquivados: assinaturas já existentes continuam valendo.

**Webhook.** A rota `POST /stripe/webhook` verifica a assinatura do Stripe sempre que `STRIPE_WEBHOOK_SECRET` estiver preenchido (em produção, sem o segredo, ela responde 503). O `.env` local vem com `STRIPE_WEBHOOK_SECRET=` vazio. Para testar localmente:

```bash
php artisan serve                       # http://127.0.0.1:8000
stripe listen --forward-to 127.0.0.1:8000/stripe/webhook
# copie o whsec_... exibido para STRIPE_WEBHOOK_SECRET no .env
php artisan config:clear
```

Em produção, crie o endpoint no painel do Stripe com os eventos `customer.subscription.created`, `customer.subscription.updated`, `customer.subscription.deleted`, `customer.updated`, `customer.deleted`, `invoice.payment_succeeded` e `invoice.payment_action_required`. Cada evento recebido limpa o cache de assinatura e faturas da conta. Cartão de teste: `4242 4242 4242 4242`, qualquer data futura e qualquer CVC.

## Arquitetura

- **Service Layer** — Lógica de negócio isolada.
- **Repository Pattern** — Operações de banco isoladas com interfaces.
- **DTO** — Transferência segura de dados entre camadas.
- **Action Pattern** — Ações isoladas e reutilizáveis.
- **Entity** — Classes com getters/setters e métodos mágicos.
- **Injeção de Dependência** — Controllers → Services → Repositories.

## Requisitos

- PHP 8.2+
- Composer 2.x
- MySQL 5.7+ ou 8.x (o código é compatível com MySQL 8.4 LTS)
- Node.js 20+ (build dos assets com Vite; obrigatório, não há CDN)

## Instalação

```bash
# 1. Clone o repositório
git clone <repo-url> Clientify360
cd Clientify360

# 2. Instale dependências PHP e publique os assets do Filament
composer install
php artisan filament:assets

# 3. Instale dependências JS e gere os assets (Vite)
npm ci
npm run build

# 4. Configure o ambiente
cp .env.example .env
php artisan key:generate

# 5. Configure no .env o banco (DB_*), o e-mail (MAIL_*), o Stripe (STRIPE_KEY,
#    STRIPE_SECRET, STRIPE_PROFESSIONAL_PRICE_ID, STRIPE_WEBHOOK_SECRET) e o reCAPTCHA

# 6. Execute as migrations e seeders
php artisan migrate --seed

# 7. Inicie o servidor e a fila
php artisan serve
php artisan queue:work   # em outro terminal (e-mails e exportações)
```

`php artisan storage:link` não é necessário: anexos, logotipos e exportações ficam em discos privados (`media` e `local`) e são servidos por rotas autenticadas.

## Comandos de qualidade

```bash
composer test        # Pest/PHPUnit (SQLite em memória)
composer lint:test   # Pint
composer stan        # PHPStan/Larastan
composer rector:dry  # Rector (somente simulação)
```

## API REST

A API versionada fica em `/api/v1` (tokens Sanctum criados em Perfil → Tokens de API, plano Profissional). A documentação OpenAPI/Swagger fica em `/api/documentation` (usuário autenticado, e-mail verificado e plano Profissional). O arquivo `storage/api-docs/api-docs.json` é versionado; ao alterar as anotações em `app/Http/Controllers/Api/V1`, rode `php artisan l5-swagger:generate` e faça commit do arquivo.

## Credenciais de Teste

O login exige os três dados juntos: nome de usuário, e-mail e senha.

| Campo | Valor |
|-------|-------|
| Nome de usuário | admin |
| E-mail | admin@clientify360.test |
| Senha | password |

## Funcionalidades

- ✅ Autenticação completa (cadastro, login, recuperação de senha, verificação de e-mail)
- ✅ Cadastro com username + email + senha
- ✅ LGPD — Consentimento explícito no cadastro
- ✅ Isolamento de dados por usuário (cada usuário vê apenas seus dados)
- ✅ CRUD completo de clientes
- ✅ Tema claro/escuro com persistência
- ✅ Interface responsiva (Mobile First)
- ✅ Landing page (hotsite) única com benefícios, funcionalidades, como usar, dicas, planos, FAQ, sobre, serviços, ferramentas e contato (WhatsApp e e-mail do Grupo 3W Online); `/sobre`, `/servicos`, `/ferramentas` e `/contato` redirecionam (301) para as seções
- ✅ Política de Privacidade e Termos de Uso; banner de cookies com consentimento registrado (LGPD, GDPR, CCPA, POPIA)
- ✅ SEO: meta tags, Open Graph, JSON-LD, `sitemap.xml`, `robots.txt` e `llms.txt`
- ✅ Páginas de erro personalizadas (401, 403, 404, 419, 429, 500, 503)
- ✅ Planos Básico (grátis) e Profissional (R$ 29,90/mês) com Stripe, menu Financeiro e histórico de pagamentos
- ✅ ACL por conta (Dono, Gestor, Colaborador) com Spatie Permission
- ✅ API REST `/api/v1` com Sanctum e documentação OpenAPI
- ✅ SweetAlert2 para feedback ao usuário e overlay de carregamento
- ✅ Breadcrumbs em todas as páginas internas
- ✅ UUID como chave primária
- ✅ SoftDeletes em todos os models, exceto as 10 exceções documentadas em `.kiro/steering/style-guide.md`

### Módulos comerciais (incorporados de `bkp-after_features`)

- ✅ **Funil de vendas (Kanban)** — oportunidades por etapa, arrastar e soltar, previsão ponderada, taxa de conversão, motivo de perda
- ✅ **Etapas do funil configuráveis** — nome, cor, ordem e probabilidade
- ✅ **Histórico de relacionamento** — ligações, reuniões, WhatsApp… com **próximo passo** que vira lembrete na Agenda
- ✅ **Tags de clientes** e **origem do lead** ("como chegou até você"), com filtro na lista
- ✅ **Produtos e serviços** — preço, promoção, custo, margem, NCM, fornecedor
- ✅ **Controle de estoque** — entradas (com nº da nota), saídas, ajuste de inventário, estoque mínimo e histórico auditável
- ✅ **Orçamentos** — itens do catálogo ou livres, desconto, validade, numeração `ORC-AAAA-0001`, impressão/PDF, duplicar
- ✅ **Aprovar orçamento = venda** — baixa estoque, gera contas a receber (parcelado), marca a oportunidade como ganha
- ✅ **Financeiro** — contas a pagar/receber, parcelamento, baixa, categorias, fluxo de caixa (realizado × previsto)
- ✅ **Captação de leads** — formulários públicos `/f/{slug}` com UTM, anti-spam e LGPD; cada envio vira cliente + oportunidade
- ✅ **Notificações** — contas vencidas, estoque baixo, novos leads e oportunidades paradas
- ✅ **Limites por plano** — `plan.limit:{recurso}` (clientes, produtos, orçamentos/mês, formulários)

Detalhes das decisões de mesclagem: [`docs/MERGE-bkp-after_features.md`](docs/MERGE-bkp-after_features.md)

## Estrutura de Diretórios

```
app/
├── Actions/          # Ações isoladas
├── Console/Commands/ # accounts:backfill, security:check, stripe:setup
├── Data/             # Objetos Laravel Data (planos, assinatura, faturas, listagens)
├── DTOs/             # Data Transfer Objects
├── Entities/         # Entidades com getters/setters
├── Exports/          # Planilhas (Laravel Excel)
├── Filament/         # Painel /app (resources e widgets)
├── Http/
│   ├── Controllers/  # Controllers (HTTP only) e Api/V1
│   ├── Middleware/   # Plano, termos, cabeçalhos de segurança, equipe
│   ├── Requests/     # Form Requests (validação)
│   └── Resources/    # API Resources (V1)
├── Interfaces/       # Contratos (interfaces)
├── Jobs/             # Jobs de fila (exportação)
├── Listeners/        # Webhook do Stripe
├── Models/           # Eloquent Models
├── Notifications/    # E-mails (verificação, senha, convites, exportação)
├── Policies/         # Autorização por conta
├── Repositories/     # Repositórios (DB operations)
├── Services/         # Serviços (business logic)
├── Support/          # Utilitários (cache por conta, SEO, CTA, privacidade)
├── Traits/           # Traits compartilhados (UUID, conta)
└── Providers/        # Service Providers
resources/
├── brand/tokens.json # Cores e tokens da identidade visual
├── css/, js/, fonts/ # Assets compilados pelo Vite
└── views/            # Blade (layouts, componentes, páginas, PDFs)
```

## Desenvolvedor

**HOTWYL | WILLFROMBRASIL**

Desenvolvido pelo **Grupo 3W Online** — [www.3wonline.com.br](https://www.3wonline.com.br)
