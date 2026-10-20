# Deploy — Clientify360 na HostGator (Hospedagem Compartilhada)

Guia de publicação em hospedagem compartilhada (cPanel/HostGator).

## 1. Pré-requisitos

- Conta HostGator com PHP 8.2+ habilitado, de preferência 8.3 (verifique em cPanel → "Selecionar versão do PHP").
- Banco MySQL 8 criado no cPanel (MySQL Databases): guarde nome do banco, usuário e senha.
- Domínio apontado para a hospedagem.

## 2. Estratégias de estrutura de pastas

O Laravel espera que o servidor sirva a pasta `public/`. Há duas abordagens:

### Opção A — Document Root apontando para `public/` (recomendada)
No cPanel, em "Domains" / "Domínios", edite o Document Root do domínio para
`.../clientify360/public`. Nesse caso o `.htaccess` da raiz do projeto **não é necessário**.

### Opção B — `.htaccess` raiz (quando não é possível mudar o Document Root)
Envie todo o projeto para a pasta servida pelo domínio. O arquivo `.htaccess` na raiz
já incluído redireciona as requisições para `public/` e força HTTPS.

## 3. Deploy automático (GitHub Actions → FTP)

O workflow `.github/workflows/deploy.yml` envia os arquivos via FTP a cada push em
`main`/`master`. Configure os secrets no repositório (Settings → Secrets → Actions):

- `FTP_HOST` — host FTP da HostGator
- `FTP_USER` — usuário FTP
- `FTP_PASSWORD` — senha FTP

> O deploy por FTP **não roda** `composer install`, `npm build` nem `migrate`.
> Como os assets (Tailwind, Alpine, SweetAlert2, Font Awesome) são carregados via CDN,
> não é necessário build de front-end. Porém `vendor/` deve ir versionado ou instalado
> no servidor (passo abaixo).

## 4. Passos pós-deploy (via Terminal do cPanel ou SSH)

```bash
# Instalar dependências de produção (se vendor/ não foi enviado)
composer install --no-dev --optimize-autoloader

# Configurar ambiente
cp .env.example .env      # apenas na primeira vez
php artisan key:generate  # apenas se APP_KEY estiver vazio

# Ajuste o .env: APP_URL, APP_DEBUG=false, credenciais do MySQL, SMTP, Stripe

# Banco de dados
php artisan migrate --force

# Otimizações de produção
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

## 5. Checklist de segurança em produção

- [ ] `APP_DEBUG=false` e `APP_ENV=production`
- [ ] `APP_KEY` gerado
- [ ] `SESSION_SECURE_COOKIE=true` (site em HTTPS)
- [ ] `SESSION_ENCRYPT=true`
- [ ] Certificado SSL ativo (AutoSSL no cPanel)
- [ ] Webhook do Stripe apontando para `https://seudominio.com.br/stripe/webhook`
- [ ] `.env` inacessível pela web (protegido pelo `.htaccess`)
- [ ] Permissões: `storage/` e `bootstrap/cache/` graváveis (755/775)

## 6. Manutenção

```bash
# Limpar caches após alteração de configuração
php artisan optimize:clear

# Reprocessar filas (se usar QUEUE_CONNECTION=database)
php artisan queue:work
```
