# Clientify360

Sistema de gestão de clientes no formato **Micro SaaS** desenvolvido com Laravel 12.

## Stack Tecnológica

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Banco de Dados | MySQL 8 |
| Frontend | Alpine.js + Tailwind CSS + HTML5 + CSS3 |
| Autenticação | Laravel Breeze |
| Alertas | SweetAlert2 |
| Ícones | Font Awesome 6 |
| Padrão CSS | Mobile First |

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
- MySQL 8.x
- Node.js 20+ (para build de assets, opcional com CDN)

## Instalação

```bash
# 1. Clone o repositório
git clone <repo-url> Clientify360
cd Clientify360

# 2. Instale dependências PHP
composer install

# 3. Configure o ambiente
cp .env.example .env
php artisan key:generate

# 4. Configure o banco de dados no .env
# DB_CONNECTION=mysql
# DB_DATABASE=clientify360
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Execute as migrations e seeders
php artisan migrate --seed

# 6. Crie o link de storage
php artisan storage:link

# 7. Inicie o servidor
php artisan serve
```

## Credenciais de Teste

| Campo | Valor |
|-------|-------|
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
- ✅ Páginas públicas (Home, Sobre, Serviços, Ferramentas, Contato, Privacidade)
- ✅ Páginas de erro personalizadas (404, 403, 500, 419, 429)
- ✅ SweetAlert2 para feedback ao usuário
- ✅ Breadcrumbs em todas as páginas internas
- ✅ UUID como chave primária
- ✅ SoftDeletes em todos os models

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
├── DTOs/             # Data Transfer Objects
├── Entities/         # Entidades com getters/setters
├── Http/
│   ├── Controllers/  # Controllers (HTTP only)
│   └── Requests/     # Form Requests (validação)
├── Interfaces/       # Contratos (interfaces)
├── Models/           # Eloquent Models
├── Repositories/     # Repositórios (DB operations)
├── Services/         # Serviços (business logic)
├── Traits/           # Traits compartilhados
└── Providers/        # Service Providers
```

## Desenvolvedor

**HOTWYL | WILLFROMBRASIL**

Desenvolvido pelo **Grupo 3W Online** — [www.3wonline.com.br](https://www.3wonline.com.br)
