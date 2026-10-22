<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\ClientRepositoryInterface;
use App\Models\Account;
use App\Repositories\ClientRepository;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);

        // Singleton: a contagem (badge do menu) e a página reaproveitam o mesmo cálculo no request.
        $this->app->singleton(NotificationService::class);
    }

    public function boot(): void
    {
        // Make visible the silent reCAPTCHA fail-open: an empty secret in production disables anti-bot.
        if ($this->app->environment('production') && blank(config('services.recaptcha.secret_key'))) {
            Log::error('reCAPTCHA secret key is empty in production: anti-bot protection is disabled (fail-open).');
        }

        // A assinatura é da conta (tenant), não do usuário: a conta é o Billable do Cashier.
        Cashier::useCustomerModel(Account::class);

        // @money($valor) → R$ 1.234,56   |   @qty($quantidade) → 1.500 / 2,5
        Blade::directive('money', fn (string $expr): string => "<?php echo e(\\App\\Support\\Format::money({$expr})); ?>");
        Blade::directive('qty', fn (string $expr): string => "<?php echo e(\\App\\Support\\Format::qty({$expr})); ?>");

        // Badge de notificações no menu lateral — só calcula quando autenticado.
        View::composer('layouts.partials.sidebar-nav', function ($view): void {
            $count = auth()->check() ? app(NotificationService::class)->count() : 0;
            $view->with('notificationCount', $count);
        });
    }
}
