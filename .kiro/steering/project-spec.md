# Clientify360 - Especificação Técnica do Projeto

## Visão Geral

Aplicação web no formato **Micro SaaS** onde cada usuário acessa apenas seus dados correspondentes. A aplicação possui ACL (Access Control List) para controle de acesso granular.

## Stack Tecnológica

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Banco de Dados | MySQL 8 |
| Frontend | Alpine.js + Tailwind CSS + HTML5 + CSS3 |
| Autenticação | Laravel Breeze |
| Alertas | SweetAlert2 |
| Ícones | Font Awesome |
| Padrão CSS | Mobile First |

## Configurações Obrigatórias

- **Idioma:** pt-BR (português brasileiro)
- **Timezone:** America/Sao_Paulo
- **Locale:** pt_BR

## Arquitetura & Padrões de Design

### Princípios
- SOLID
- Clean Code
- DRY (Don't Repeat Yourself)

### Padrões Estruturais
- **Service Layer:** Lógica de negócio fora dos controllers
- **Repository Pattern:** Operações de banco de dados isoladas
- **DTO (Data Transfer Object):** Transferência segura de dados entre camadas
- **Action Pattern:** Ações isoladas e reutilizáveis
- **Injeção de Dependência:** Ligação entre controllers, services e repositories
- **Interfaces:** Contratos para repositórios e serviços
- **Traits:** Funcionalidades comuns compartilhadas entre classes
- **Entities:** Classes com getters/setters e métodos mágicos (__get, __set)

### Estrutura de Diretórios Esperada
```
app/
├── Actions/
├── DTOs/
├── Entities/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Interfaces/
├── Models/
├── Repositories/
├── Services/
└── Traits/
```

## Banco de Dados

### Regras
- UUID como chave primária em todas as tabelas
- SoftDeletes em todos os models
- Indexes para otimização e performance
- Schemas normalizados com chaves primárias e estrangeiras vinculadas
- Stored Procedures, Functions, Triggers e Views quando necessário

### Estrutura por Entidade
1. **Model** - UUID + SoftDeletes + Traits
2. **Migration** - UUID PK + Indexes + SoftDeletes
3. **Factory** - Dados de exemplo para todos os campos
4. **Seeder** - Popular tabela usando Factory
5. **Route** - API Resource (CRUD completo)
6. **Requests** - Validação para criação e atualização
7. **Resources** - Tratamento de retornos
8. **Controller** - Métodos Resource (index, show, store, update, destroy)

## Autenticação & Segurança

### Autenticação
- Laravel Breeze
- Credenciais: usuário, email e senha
- Cadastro com confirmação por email
- Login e Reset de senha
- Modelo User com UUID e SoftDeletes

### ACL & Isolamento de Dados
- Cada usuário acessa SOMENTE seus próprios dados e registros
- Implementar middleware/policy para garantir isolamento
- Scopes globais para filtrar dados por usuário autenticado

### Conformidade LGPD
- Consentimento explícito para coleta de dados
- Possibilidade de exclusão de dados pessoais
- Política de privacidade acessível
- Logs de acesso a dados sensíveis

## Interface & UX/UI

### Design
- Layout moderno e profissional
- Template com header e footer em TODAS as páginas
- Tema claro/escuro com alternância
- Breadcrumb para navegação
- Tooltips e botões de ajuda
- Descrições, exemplos de uso, dicas e orientações ao longo da interface
- Responsividade total (Mobile First)

### Formulários
- `autocomplete="off"` em todos os inputs
- Validação client-side e server-side
- Feedback visual com SweetAlert2

### Rodapé
- Texto: "Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL"
- Presente em todas as páginas

### SEO
- Meta tags otimizadas
- Estrutura semântica HTML5
- Performance otimizada

## Páginas Públicas (Landing Page / Hotsite)

### Estrutura
- **Home** - Landing page destacando benefícios, funcionalidades, instruções de uso e dicas
- **Sobre** - Informações sobre o sistema
- **Serviços** - Serviços oferecidos
- **Ferramentas** - Ferramentas disponíveis
- **Contato** - Formulário de contato via iframe

### CTA (Call to Action)
- Copy persuasiva em cada página
- Empresa desenvolvedora: Grupo 3W Online (desenvolve sites e sistemas personalizados sob demanda)
- Link dos CTAs: www.3wonline.com.br

## Páginas de Erro

- 404 - Página não encontrada
- 403 - Acesso negado
- 500 - Erro interno
- 419 - Sessão expirada
- 429 - Muitas requisições

## Checklist de Qualidade

- [ ] Todos os botões e links funcionando corretamente
- [ ] Navegação testada em todas as páginas
- [ ] Autenticação testada (cadastro, login, recuperação de senha)
- [ ] Isolamento de dados por usuário verificado
- [ ] Aplicação 100% em português (pt-BR)
- [ ] Responsividade testada em dispositivos móveis
- [ ] Tema claro/escuro funcionando
- [ ] Performance otimizada
- [ ] Segurança validada
- [ ] Conformidade LGPD implementada

## Passos de Instalação

1. Instalar Laravel 12 com Composer
2. Configurar `.env` (MySQL, timezone, locale)
3. Instalar Laravel Breeze
4. Instalar pacotes de tradução pt-BR
5. Configurar Alpine.js + Tailwind CSS
6. Instalar SweetAlert2 + Font Awesome
7. Executar migrations e seeders
8. Testar autenticação e navegação
