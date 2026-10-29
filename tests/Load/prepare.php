<?php

declare(strict_types=1);

/*
 * Prepares the disposable load-test environment (composer test:load:prepare):
 * a gitignored .env.loadtest pointing to database/loadtest.sqlite, raised rate
 * limits, no Stripe/reCAPTCHA/analytics keys, then migrate + LoadTestSeeder.
 * It never touches the database configured in .env.
 */

$root = dirname(__DIR__, 2);
chdir($root);

function artisan(string $arguments): string
{
    exec(PHP_BINARY.' artisan '.$arguments.' 2>&1', $output, $code);
    $text = implode(PHP_EOL, $output);
    if ($code !== 0) {
        fwrite(STDERR, $text.PHP_EOL);

        throw new RuntimeException("php artisan {$arguments} failed ({$code}).");
    }

    return $text;
}

// 0. A cached config would make the server ignore .env.loadtest.
artisan('config:clear');

// 1. Reuse the local APP_KEY without printing it.
$env = (string) @file_get_contents($root.'/.env');
if (!preg_match('/^APP_KEY=([^\r\n]+)/m', $env, $match) || trim($match[1], " \t\"'") === '') {
    throw new RuntimeException('APP_KEY not found in .env.');
}
$match[1] = trim($match[1], " \t\"'");

$database = str_replace('\\', '/', $root.'/database/loadtest.sqlite');

// 2. The disposable environment file.
file_put_contents($root.'/.env.loadtest', implode(PHP_EOL, [
    'APP_NAME=Clientify360',
    'APP_ENV=loadtest',
    'APP_DEBUG=false',
    'APP_KEY='.$match[1],
    'APP_URL=http://127.0.0.1:8000',
    'APP_LOCALE=pt_BR',
    'APP_FALLBACK_LOCALE=pt_BR',
    'LOG_CHANNEL=single',
    'LOG_LEVEL=error',
    'DB_CONNECTION=sqlite',
    'DB_DATABASE='.$database,
    'DB_URL=',
    'CACHE_STORE=file',
    'SESSION_DRIVER=file',
    'SESSION_SECURE_COOKIE=false',
    'QUEUE_CONNECTION=sync',
    'MAIL_MAILER=log',
    'SCOUT_DRIVER=database',
    'STRIPE_KEY=',
    'STRIPE_SECRET=',
    'STRIPE_WEBHOOK_SECRET=',
    'RECAPTCHA_SITE_KEY=',
    'RECAPTCHA_SECRET_KEY=',
    'GTM_ID=',
    'GA4_ID=',
    'API_RATE_LIMIT=100000',
    'PUBLIC_RATE_LIMIT=100000',
    'APP_RATE_LIMIT=100000',
]).PHP_EOL);

// Safety: the loadtest environment must resolve to the SQLite file before anything runs.
$default = trim(artisan('config:show database.default --env=loadtest'));
if (!str_contains($default, 'sqlite')) {
    throw new RuntimeException('The loadtest environment does not resolve to SQLite; aborting.');
}

// 3. Fresh disposable database file (no migrate:fresh on any server DB).
if (is_file($database)) {
    unlink($database);
}
touch($database);

// 4. Schema and data.
artisan('migrate --env=loadtest --force');
artisan('db:seed --env=loadtest --class="Database\\Seeders\\LoadTestSeeder" --force');

fwrite(STDOUT, 'Load-test environment ready (SQLite: database/loadtest.sqlite).'.PHP_EOL);
