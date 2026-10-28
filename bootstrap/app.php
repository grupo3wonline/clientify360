<?php

declare(strict_types=1);

use App\Http\Middleware\CheckPlanLimit;
use App\Http\Middleware\EnsureTermsAccepted;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetSpatieTeam;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        // Define o contexto de equipe (conta) do Spatie Permission por request.
        $middleware->web(append: [
            SetSpatieTeam::class,
        ]);

        $middleware->alias([
            'plan.limit' => CheckPlanLimit::class,
            'terms.accepted' => EnsureTermsAccepted::class,
            // ACL por conta (Spatie Permission)
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // The cookie-consent cookie is read by client-side JS, so it stays unencrypted.
        $middleware->encryptCookies(except: [
            'c360_consent',
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
