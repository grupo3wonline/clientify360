<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Notifications\Notification;

/**
 * Applies the plan limits (config/plans.php) to Filament create pages, the
 * same rule the Blade routes enforce with the `plan.limit` middleware.
 */
trait EnforcesPlanLimit
{
    /** Resource key in config/plans.php "limits" (e.g. "clients"). */
    abstract protected static function planResource(): string;

    public function mount(): void
    {
        if (!$this->planAllowsCreation()) {
            $this->notifyPlanLimit();
            $this->redirect(static::getResource()::getUrl('index'));

            return;
        }

        parent::mount();
    }

    /** Guards against usage changing between mount and save. */
    protected function beforeCreate(): void
    {
        if ($this->planAllowsCreation()) {
            return;
        }

        $this->notifyPlanLimit();
        $this->halt();
    }

    private function planAllowsCreation(): bool
    {
        $user = auth()->user();

        return !$user instanceof User
            || app(SubscriptionService::class)->canCreate($user, static::planResource());
    }

    private function notifyPlanLimit(): void
    {
        $user = auth()->user();

        Notification::make()
            ->danger()
            ->title('Limite do plano atingido')
            ->body($user instanceof User ? app(SubscriptionService::class)->limitMessage($user, static::planResource()) : null)
            ->send();
    }
}
