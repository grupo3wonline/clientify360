<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Responses\FilamentLogoutResponse;
use App\Interfaces\ClientRepositoryInterface;
use App\Models\Account;
use App\Models\PersonalAccessToken;
use App\Repositories\ClientRepository;
use App\Services\NotificationService;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Cashier\Cashier;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);

        // Singleton: a contagem (badge do menu) e a página reaproveitam o mesmo cálculo no request.
        $this->app->singleton(NotificationService::class);

        // The app registers its own guarded /stripe/webhook route (routes/web.php).
        Cashier::ignoreRoutes();

        // The panel has no login page: logging out of /app goes to the home page.
        $this->app->bind(LogoutResponse::class, FilamentLogoutResponse::class);
    }

    public function boot(): void
    {
        // Make visible the silent reCAPTCHA fail-open: an empty secret in production disables anti-bot.
        if ($this->app->environment('production') && blank(config('services.recaptcha.secret_key'))) {
            Log::error('reCAPTCHA secret key is empty in production: anti-bot protection is disabled (fail-open).');
        }

        // A assinatura é da conta (tenant), não do usuário: a conta é o Billable do Cashier.
        Cashier::useCustomerModel(Account::class);

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // N+1 guard: lazy loading throws in local/testing; production stays permissive.
        Model::preventLazyLoading(!$this->app->isProduction());

        // autocomplete="off" on every Filament text field (project rule for all forms and inputs).
        TextInput::configureUsing(fn (TextInput $component): TextInput => $component->autocomplete(false));
        Textarea::configureUsing(fn (Textarea $component): Textarea => $component->autocomplete(false));

        $this->configurePasswordPolicy();
        $this->configureRateLimiters();

        // @money($valor) → R$ 1.234,56   |   @qty($quantidade) → 1.500 / 2,5
        Blade::directive('money', fn (string $expr): string => "<?php echo e(\\App\\Support\\Format::money({$expr})); ?>");
        Blade::directive('qty', fn (string $expr): string => "<?php echo e(\\App\\Support\\Format::qty({$expr})); ?>");
        // Same as Blade's @method, plus autocomplete="off" (project rule: every input has it; @csrf already does).
        Blade::directive('method', fn (string $expr): string => "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"'.e(strtoupper({$expr})).'\" autocomplete=\"off\">'; ?>");

        // Badge de notificações no menu lateral — só calcula quando autenticado.
        View::composer('layouts.partials.sidebar-nav', function ($view): void {
            $count = auth()->check() ? app(NotificationService::class)->count() : 0;
            $view->with('notificationCount', $count);
        });
    }

    /**
     * Production requires 10+ characters with mixed case, numbers and a HIBP
     * check (fail-open if the API is unreachable). Elsewhere: 8+ characters.
     */
    private function configurePasswordPolicy(): void
    {
        Password::defaults(fn (): Password => $this->app->isProduction()
            ? Password::min(10)->mixedCase()->numbers()->uncompromised()
            : Password::min(8));
    }

    /**
     * Named limiters. Limits come from config so only the disposable load-test
     * environment can raise them.
     */
    private function configureRateLimiters(): void
    {
        // Per-IP cap on login POSTs, on top of LoginRequest's 5 attempts per credential tuple.
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(20)->by($request->ip()));

        RateLimiter::for('public', fn (Request $request): Limit => Limit::perMinute(
            (int) config('services.rate_limits.public', 60)
        )->by($request->ip()));

        RateLimiter::for('app', fn (Request $request): Limit => Limit::perMinute(
            (int) config('services.rate_limits.app', 120)
        )->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(
            (int) config('services.rate_limits.api', 60)
        )->by($request->user()?->id ?: $request->ip())
            ->response(fn (Request $request, array $headers) => response()->json(
                ['message' => 'Muitas requisições. Tente novamente em instantes.'],
                429,
                $headers,
            )));
    }
}
