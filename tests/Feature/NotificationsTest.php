<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DismissedNotification;
use App\Models\FinancialEntry;
use App\Models\Reminder;
use App\Models\User;
use App\Services\AccountService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_items_generate_notifications(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Reminder::create(['title' => 'Ligar para cliente', 'remind_at' => now()->subDay(), 'is_completed' => false]);
        FinancialEntry::create(['direction' => 'payable', 'description' => 'Internet', 'amount' => 100, 'due_date' => now()->subDays(2), 'status' => 'pending']);

        $count = app(NotificationService::class)->count();
        $this->assertSame(2, $count);

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Lembrete atrasado')
            ->assertSee('Conta a pagar vencida');
    }

    public function test_dismissing_a_notification_hides_it(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reminder = Reminder::create(['title' => 'Reunião', 'remind_at' => now()->subDay(), 'is_completed' => false]);
        $key = 'reminder-'.$reminder->id;

        $this->post(route('notifications.markRead', $key))->assertRedirect(route('notifications.index'));

        $this->assertDatabaseHas('dismissed_notifications', [
            'user_id' => $user->id,
            'notification_key' => $key,
        ]);

        $this->assertSame(0, app(NotificationService::class)->count());
    }

    public function test_dismiss_all_hides_every_current_notification(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $r1 = Reminder::create(['title' => 'A', 'remind_at' => now()->subDay(), 'is_completed' => false]);
        $r2 = Reminder::create(['title' => 'B', 'remind_at' => now()->subDays(2), 'is_completed' => false]);

        $this->post(route('notifications.markAllRead'), [
            'keys' => ['reminder-'.$r1->id, 'reminder-'.$r2->id],
        ])->assertRedirect(route('notifications.index'));

        $this->assertSame(0, app(NotificationService::class)->count());
    }

    public function test_dismissals_are_isolated_per_user(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $reminder = Reminder::create(['title' => 'Privado', 'remind_at' => now()->subDay(), 'is_completed' => false]);
        $key = 'reminder-'.$reminder->id;
        DismissedNotification::create(['user_id' => $owner->id, 'notification_key' => $key, 'dismissed_at' => now()]);

        // Outro usuário não enxerga as dispensas (nem os lembretes) do dono.
        $other = User::factory()->create();
        $this->actingAs($other);
        $this->assertSame(0, DismissedNotification::count());
    }

    public function test_it_dismisses_notifications_per_user_within_the_same_account(): void
    {
        // Dois colaboradores na MESMA conta (tenant).
        $owner = User::factory()->create();
        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $owner->account, User::ROLE_COLLABORATOR);

        // Alerta compartilhado pela conta: um lembrete atrasado.
        $this->actingAs($owner);
        $reminder = Reminder::create(['title' => 'Alerta da equipe', 'remind_at' => now()->subDay(), 'is_completed' => false]);
        $key = 'reminder-'.$reminder->id;

        // O NotificationService é singleton e mantém um cache por request;
        // reinstanciamos a cada checagem para simular requests independentes.
        $freshCount = function (): int {
            $this->app->forgetInstance(NotificationService::class);

            return app(NotificationService::class)->count();
        };

        // Ambos veem o alerta antes de qualquer dispensa.
        $this->assertSame(1, $freshCount());

        $this->actingAs($collaborator->fresh());
        $this->assertSame(1, $freshCount());

        // O owner (A) dispensa o alerta.
        $this->actingAs($owner);
        $this->post(route('notifications.markRead', $key))->assertRedirect(route('notifications.index'));
        $this->assertSame(0, $freshCount());

        // O colaborador (B), na mesma conta, continua vendo o alerta.
        $this->actingAs($collaborator->fresh());
        $this->assertSame(1, $freshCount());

        $this->app->forgetInstance(NotificationService::class);
        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Alerta da equipe');
    }
}
