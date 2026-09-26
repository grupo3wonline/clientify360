<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\ClientRepositoryInterface;
use App\Repositories\ClientRepository;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
    }

    public function boot(): void
    {
        // @money($valor) → R$ 1.234,56   |   @qty($quantidade) → 1.500 / 2,5
        Blade::directive('money', fn (string $expr): string => "<?php echo e(\\App\\Support\\Format::money({$expr})); ?>");
        Blade::directive('qty', fn (string $expr): string => "<?php echo e(\\App\\Support\\Format::qty({$expr})); ?>");
    }
}
