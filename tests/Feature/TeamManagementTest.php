<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AccountInvitation;
use App\Models\User;
use App\Notifications\AccountInvitationNotification;
use App\Services\AccountService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_account_and_owner_role(): void
    {
        $this->post(route('register'), [
            'name' => 'Dona da Empresa',
            'username' => 'dona',
            'email' => 'dona@empresa.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'lgpd_consent' => '1',
        ]);

        $user = User::where('email', 'dona@empresa.com')->firstOrFail();
        $this->assertNotNull($user->account_id);
        $this->assertSame($user->id, $user->account->owner_id);

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->account_id);
        $this->assertTrue($user->fresh()->hasRole(User::ROLE_OWNER));
    }

    public function test_owner_can_invite_a_collaborator(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->post(route('team.invite'), ['email' => 'novo@ex.com', 'role' => User::ROLE_COLLABORATOR])
            ->assertRedirect(route('team.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('account_invitations', [
            'account_id' => $owner->account_id,
            'email' => 'novo@ex.com',
            'role' => User::ROLE_COLLABORATOR,
        ]);
    }

    public function test_registering_with_invite_token_joins_the_account(): void
    {
        $owner = User::factory()->create();
        $invitation = app(AccountService::class)->invite($owner->account, 'convidado@ex.com', User::ROLE_MANAGER, $owner);

        $this->post(route('register'), [
            'name' => 'Convidado',
            'username' => 'convidado',
            'email' => 'convidado@ex.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'lgpd_consent' => '1',
            'invite_token' => $invitation->token,
        ]);

        $member = User::where('email', 'convidado@ex.com')->firstOrFail();
        $this->assertSame($owner->account_id, $member->account_id);
        $this->assertNotNull($invitation->fresh()->accepted_at);

        app(PermissionRegistrar::class)->setPermissionsTeamId($owner->account_id);
        $this->assertTrue($member->fresh()->hasRole(User::ROLE_MANAGER));
    }

    public function test_collaborator_cannot_access_team_management(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $owner->account, User::ROLE_COLLABORATOR);

        $this->actingAs($collaborator->fresh());

        $this->post(route('team.invite'), ['email' => 'x@ex.com', 'role' => User::ROLE_COLLABORATOR])
            ->assertForbidden();
    }

    public function test_owner_cannot_be_removed(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->delete(route('team.removeMember', $owner))->assertForbidden();
    }

    public function test_owner_can_remove_a_collaborator(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $owner->account, User::ROLE_COLLABORATOR);

        $this->actingAs($owner);
        $this->delete(route('team.removeMember', $collaborator))
            ->assertRedirect(route('team.index'));

        $this->assertNull($collaborator->fresh()->account_id);
    }

    public function test_removing_a_member_deletes_their_api_tokens(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $owner->account, User::ROLE_COLLABORATOR);
        $collaborator->createToken('integração', ['clients:read']);
        $this->assertSame(1, $collaborator->tokens()->count());

        $this->actingAs($owner);
        $this->delete(route('team.removeMember', $collaborator))->assertRedirect(route('team.index'));

        $this->assertSame(0, $collaborator->tokens()->count());
    }

    public function test_collaborator_cannot_open_the_team_page(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $owner->account, User::ROLE_COLLABORATOR);

        $this->actingAs($collaborator->fresh())->get(route('team.index'))->assertForbidden();
    }

    public function test_invite_sends_the_invitation_email(): void
    {
        Notification::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('team.invite'), ['email' => 'Novo@Ex.com', 'role' => User::ROLE_MANAGER])
            ->assertSessionHas('success', 'Convite enviado para Novo@Ex.com.');

        $token = AccountInvitation::where('email', 'novo@ex.com')->value('token');

        Notification::assertSentOnDemand(
            AccountInvitationNotification::class,
            function (AccountInvitationNotification $notification, array $channels, object $notifiable) use ($owner, $token): bool {
                $mail = $notification->toMail($notifiable);
                $body = implode(' ', $mail->introLines).' '.implode(' ', $mail->outroLines);

                return $notifiable->routes['mail'] === 'novo@ex.com'
                    && $notification instanceof ShouldQueue
                    && $mail->actionText === 'Aceitar convite'
                    && $mail->actionUrl === route('register', ['invite' => $token])
                    && str_contains($body, "convidou você para a conta {$owner->account->name} no Clientify360 como Gestor.")
                    && str_contains($body, 'O convite expira em 7 dias.');
            },
        );
    }
}
