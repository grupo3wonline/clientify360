<?php

declare(strict_types=1);

namespace Tests\Load\Support;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\Factory;
use RuntimeException;

/**
 * Safety checks run before any load scenario: the target must be the local
 * server, it must be up, and it must be serving the disposable SQLite database
 * (proved by the marker client that only LoadTestSeeder creates).
 */
final class Preflight
{
    /** @var array{base: string, token: string, cookie: string}|null */
    private static ?array $context = null;

    /** @return array{base: string, token: string, cookie: string} */
    public static function ensure(): array
    {
        return self::$context ??= self::run();
    }

    public static function url(string $path): string
    {
        return self::ensure()['base'].$path;
    }

    public static function assertLocal(string $base): void
    {
        $host = parse_url($base, PHP_URL_HOST);
        if (!in_array($host, ['127.0.0.1', 'localhost'], true) || parse_url($base, PHP_URL_SCHEME) !== 'http') {
            throw new RuntimeException('Load tests run only against the local server.');
        }
    }

    /** @return array{base: string, token: string, cookie: string} */
    private static function run(): array
    {
        $base = rtrim((string) (getenv('LOAD_BASE_URL') ?: 'http://127.0.0.1:8000'), '/');
        self::assertLocal($base); // before any request

        $http = new Factory;
        if ($http->timeout(10)->get($base.'/up')->status() !== 200) {
            throw new RuntimeException('Start the local server first (php artisan serve --host=127.0.0.1 --port=8000).');
        }

        $file = dirname(__DIR__, 3).'/storage/app/private/loadtest-credentials.json';
        $credentials = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
        if (!is_array($credentials)) {
            throw new RuntimeException('Run composer test:load:prepare first.');
        }

        $marker = $http->withToken($credentials['token'])->acceptJson()
            ->get($base.'/api/v1/clients', ['search' => 'LOADTEST-MARKER'])->json('data.0.name');
        if ($marker !== 'LOADTEST-MARKER') {
            throw new RuntimeException('The server is not using the load-test SQLite database.');
        }

        return ['base' => $base, 'token' => $credentials['token'], 'cookie' => self::login($http, $base, $credentials)];
    }

    /** @param  array{username: string, email: string, password: string}  $credentials */
    private static function login(Factory $http, string $base, array $credentials): string
    {
        $jar = new CookieJar;
        $html = $http->withOptions(['cookies' => $jar])->get($base.'/login')->body();
        if (!preg_match('/name="_token" value="([^"]+)"/', $html, $token)) {
            throw new RuntimeException('CSRF token not found on /login.');
        }

        $response = $http->withOptions(['cookies' => $jar])->withoutRedirecting()->asForm()->post($base.'/login', [
            '_token' => $token[1],
            'username' => $credentials['username'],
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);
        if ($response->status() !== 302 || str_contains((string) $response->header('Location'), '/login')) {
            throw new RuntimeException('Session login failed for the load-test user.');
        }

        $cookies = [];
        foreach ($jar->toArray() as $cookie) {
            if (str_ends_with($cookie['Name'], '-session') || $cookie['Name'] === 'XSRF-TOKEN') {
                $cookies[] = $cookie['Name'].'='.$cookie['Value'];
            }
        }

        $cookie = implode('; ', $cookies);

        // k6 follows redirects, so a dead session would silently measure /login: prove it works.
        $dashboard = (new Factory)->withHeaders(['Cookie' => $cookie])->withoutRedirecting()->get($base.'/app');
        if ($dashboard->status() !== 200) {
            throw new RuntimeException('The session cookie does not open /app (status '.$dashboard->status().').');
        }

        return $cookie;
    }
}
