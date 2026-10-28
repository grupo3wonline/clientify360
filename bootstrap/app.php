<?php

declare(strict_types=1);

use App\Http\Middleware\CheckPlanLimit;
use App\Http\Middleware\EnsurePlanFeature;
use App\Http\Middleware\EnsureTermsAccepted;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetSpatieTeam;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            'plan.feature' => EnsurePlanFeature::class,
            'terms.accepted' => EnsureTermsAccepted::class,
            // ACL por conta (Spatie Permission)
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            // Sanctum token abilities (API v1)
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
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
        // API v1 error envelope: always JSON {"message": "..."} in pt-BR, never a trace.
        // Laravel converts AuthorizationException/MissingAbilityException to
        // AccessDeniedHttpException and ModelNotFoundException to NotFoundHttpException
        // before these callbacks run, so only the converted types are handled here.
        // The Swagger UI (api/documentation) is a browser page: guests go to /login, Básico gets the 403 page.
        $isApi = fn (Request $request): bool => $request->is('api/*')
            && !$request->is('api/documentation', 'api/documentation/*', 'api/oauth2-callback');

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $isApi($request) || $request->expectsJson()
        );

        $json = fn (string $message, int $status, array $headers = []): JsonResponse => response()->json(['message' => $message], $status, $headers);

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($json, $isApi): ?JsonResponse {
            return $isApi($request) ? $json('Não autenticado.', 401) : null;
        });

        $exceptions->render(function (UnauthorizedException $e, Request $request) use ($json, $isApi): ?JsonResponse {
            return $isApi($request) ? $json('Você não tem permissão para esta ação.', 403) : null;
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($json, $isApi): ?JsonResponse {
            if (!$isApi($request)) {
                return null;
            }

            return $e->getPrevious() instanceof MissingAbilityException
                ? $json('O token não possui permissão para esta ação.', 403)
                : $json(__($e->getMessage() ?: 'Acesso negado.'), 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($json, $isApi): ?JsonResponse {
            return $isApi($request) ? $json('Registro não encontrado.', 404) : null;
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($json, $isApi): ?JsonResponse {
            return $isApi($request)
                ? $json(__($e->getMessage() ?: 'Erro na requisição.'), $e->getStatusCode(), $e->getHeaders())
                : null;
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($json, $isApi): ?JsonResponse {
            if (!$isApi($request) || $e instanceof ValidationException || $e instanceof HttpResponseException
                || $e instanceof HttpExceptionInterface || $e instanceof AuthenticationException) {
                return null;
            }

            return $json('Erro interno. Tente novamente mais tarde.', 500);
        });
    })->create();
