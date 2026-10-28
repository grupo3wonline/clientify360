# Deploy — Clientify360 na HostGator (Hospedagem Compartilhada)

Guia de publicação em hospedagem compartilhada (cPanel/HostGator).

## 1. Pré-requisitos

- Conta HostGator com PHP 8.2+ habilitado (cPanel → "Selecionar versão do PHP" / MultiPHP).
- Banco MySQL criado no cPanel (MySQL Databases): guarde nome do banco, usuário e senha. O código é compatível com MySQL 5.7 e 8.x.
- Domínio apontado para a hospedagem.
- Extensões PHP (confira com `php -m`):
  - `exif` e `fileinfo` (anexos e logotipo — Media Library)
  - `intl` (formatação de valores do Cashier/Stripe)
  - `dom` e `mbstring` (PDF — dompdf)
  - `gd` (imagens PNG no PDF)
  - `zip` (planilhas Excel)
  - `pdo_mysql`, `curl`, `openssl`

  `php artisan security:check` falha em produção com "Extensão PHP ausente: {nome}" quando falta `exif`, `fileinfo`, `intl`, `gd` ou `zip`.

## 2. Estratégias de estrutura de pastas

O Laravel espera que o servidor sirva a pasta `public/`. Há duas abordagens:

### Opção A — Document Root apontando para `public/` (recomendada)
No cPanel, em "Domains" / "Domínios", edite o Document Root do domínio para
`.../clientify360/public`. Nesse caso o `.htaccess` da raiz do projeto **não é necessário**.

### Opção B — `.htaccess` raiz (quando não é possível mudar o Document Root)
Envie todo o projeto para a pasta servida pelo domínio. O arquivo `.htaccess` na raiz
já incluído redireciona as requisições para `public/` e força HTTPS.

## 3. Envio dos arquivos (FTP)

O workflow `.github/workflows/deploy.yml` faz apenas checkout + sincronização por FTP a cada push em
`main`/`master` (secrets `FTP_HOST`, `FTP_USER`, `FTP_PASSWORD`). Ele **não** roda `composer install`,
`npm run build` nem comandos `artisan`, e as pastas `vendor/`, `public/build/` e os assets do Filament
estão no `.gitignore`. Escolha uma das opções:

- **(a) Build local + upload.** Na sua máquina:
  ```bash
  composer install --no-dev --optimize-autoloader
  php artisan filament:assets
  npm ci && npm run build
  ```
  Depois envie por FTP as pastas `vendor/`, `public/build/`, `public/js/filament/`, `public/css/filament/`
  e `public/fonts/filament/`. Sem as três pastas do Filament o painel `/app` abre sem CSS e JS.
- **(b) Restaurar as etapas de build no workflow.** Volte a ter no `deploy.yml` os passos de
  `composer install` e `npm run build` (como na versão anterior do arquivo) e acrescente
  `php artisan filament:assets` logo após o `composer install`, para que a sincronização FTP leve
  `vendor/`, os assets compilados e os do Filament.

A documentação da API (`storage/api-docs/api-docs.json`) vai versionada no repositório: não é preciso
rodar `l5-swagger:generate` em produção.

## 4. Passos pós-deploy (via Terminal do cPanel ou SSH)

Sem SSH, use uma tarefa cron temporária para rodar cada comando uma vez.

```bash
# Configurar ambiente (primeira vez)
cp .env.example .env
php artisan key:generate   # apenas se APP_KEY estiver vazio
# Ajuste o .env: APP_URL (https), APP_DEBUG=false, MySQL, SMTP, Stripe, STRIPE_WEBHOOK_SECRET

php artisan migrate --force
php artisan filament:assets      # sem efeito quando os arquivos já foram enviados
php artisan optimize             # caches de config, rotas, views e eventos
php artisan filament:optimize
php artisan storage:link
php artisan security:check       # deve terminar sem erros
```

## 5. Agendador e filas (cron)

E-mails de verificação, redefinição de senha, convites de equipe e as planilhas Excel são processados
pela fila (`QUEUE_CONNECTION=database`). Sem o cron nada disso é enviado. No cPanel → Cron Jobs, crie
uma tarefa a cada minuto usando o binário do PHP 8.2 do MultiPHP (não `/usr/local/bin/php`, que segue a
versão padrão do servidor e pode ser antiga):

```
* * * * * cd /home/USUARIO/clientify360 && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> storage/logs/cron.log 2>&1
```

Confirme o caminho com `ls /opt/cpanel/` e `/opt/cpanel/ea-php82/root/usr/bin/php -v`. Mantenha a saída em
`storage/logs/cron.log` até validar o funcionamento; depois troque por `/dev/null`.

O agendador (`routes/console.php`) roda a cada minuto `queue:work --stop-when-empty --max-time=50 --tries=3`
e, uma vez por dia, `sanctum:prune-expired --hours=24` (tokens de API vencidos), `model:prune` e a limpeza
das planilhas exportadas com mais de 2 dias.

**Se `proc_open` estiver desabilitado** (o `security:check` avisa com "Função PHP `proc_open` desabilitada:
o agendador não executa comandos"), troque as entradas por
`Schedule::call(fn () => Artisan::call('queue:work', ['--stop-when-empty' => true, '--max-time' => 50, '--tries' => 3]))->everyMinute()->withoutOverlapping(2)`
(mesmo padrão para `sanctum:prune-expired` e `model:prune`), que rodam no mesmo processo, ou adicione uma
segunda linha de cron que execute a fila diretamente:

```
* * * * * cd /home/USUARIO/clientify360 && /opt/cpanel/ea-php82/root/usr/bin/php artisan queue:work --stop-when-empty --max-time=50 >> storage/logs/cron.log 2>&1
```

**Verificação pós-deploy:** cadastre um usuário de teste e confirme que o e-mail de verificação chega em até
2 minutos. Se não chegar, veja `storage/logs/cron.log` e `php artisan queue:failed`.

## 6. Checklist de segurança em produção

- [ ] `APP_DEBUG=false` e `APP_ENV=production`
- [ ] `APP_KEY` gerado
- [ ] `SESSION_SECURE_COOKIE=true` (site em HTTPS)
- [ ] `SESSION_ENCRYPT=true`
- [ ] Certificado SSL ativo (AutoSSL no cPanel)
- [ ] Webhook do Stripe apontando para `https://seudominio.com.br/stripe/webhook` e `STRIPE_WEBHOOK_SECRET` preenchido
- [ ] `.env` inacessível pela web (protegido pelo `.htaccess`)
- [ ] Permissões: `storage/` e `bootstrap/cache/` graváveis (755/775)
- [ ] `php artisan security:check` sem erros

## 7. Manutenção

```bash
# Limpar caches após alteração de configuração
php artisan optimize:clear && php artisan optimize

# Filas: ver e reenviar jobs que falharam
php artisan queue:failed
php artisan queue:retry all
```
