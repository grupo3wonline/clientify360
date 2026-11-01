<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\AccountInvitation;
use App\Models\User;
use App\Notifications\AccountInvitationNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Provisiona contas (tenants) e seus papéis escopados por conta, e vincula
 * usuários a uma conta com um papel. Centraliza a regra de ACL por equipe.
 */
final class AccountService
{
    public function __construct(
        private readonly PermissionRegistrar $registrar
    ) {}

    /**
     * Cria uma conta nova, define o usuário como dono e provisiona os papéis.
     */
    public function provisionForOwner(User $owner, ?string $name = null): Account
    {
        return DB::transaction(function () use ($owner, $name): Account {
            $account = Account::create([
                'name' => $name ?: $this->defaultName($owner),
                'owner_id' => $owner->id,
            ]);

            $owner->account_id = $account->id;
            $owner->save();

            $this->ensureRoles($account);
            $this->assignRole($owner, User::ROLE_OWNER, $account);

            return $account;
        });
    }

    /**
     * Garante que o usuário tenha uma conta (idempotente).
     *
     * Reaproveita provisionForOwner() apenas quando account_id está nulo,
     * servindo de salvaguarda para usuários órfãos (seeds antigos, dados
     * migrados) sem recriar contas de quem já tem uma.
     */
    public function ensureProvisioned(User $user): Account
    {
        if ($user->account_id !== null) {
            return $user->account()->firstOrFail();
        }

        return $this->provisionForOwner($user);
    }

    /**
     * Garante que os 3 papéis existam para a conta, com as permissões corretas.
     */
    public function ensureRoles(Account $account): void
    {
        $this->ensurePermissionCatalog();
        $this->registrar->setPermissionsTeamId($account->id);

        foreach (RolesAndPermissionsSeeder::rolePermissions() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        $this->registrar->forgetCachedPermissions();
    }

    /**
     * Vincula um usuário a uma conta e define seu papel.
     */
    public function addMember(User $user, Account $account, string $role): void
    {
        $user->account_id = $account->id;
        $user->save();

        $this->assignRole($user, $role, $account);
    }

    /**
     * Define o papel do usuário dentro da conta (substitui papéis anteriores).
     */
    public function assignRole(User $user, string $role, Account $account): void
    {
        $this->registrar->setPermissionsTeamId($account->id);
        $user->syncRoles([$role]);
        $this->registrar->forgetCachedPermissions();
    }

    /**
     * Cria (ou renova) um convite para um e-mail entrar na conta com um papel.
     */
    public function invite(Account $account, string $email, string $role, ?User $invitedBy = null): AccountInvitation
    {
        $email = mb_strtolower(trim($email));

        $invitation = AccountInvitation::updateOrCreate(
            ['account_id' => $account->id, 'email' => $email],
            [
                'role' => $role,
                'invited_by' => $invitedBy?->id,
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ],
        );

        Notification::route('mail', $email)
            ->notify(AccountInvitationNotification::for($invitation, $account->name, $invitedBy));

        return $invitation;
    }

    /** Remove um colaborador da conta (não pode remover o dono). */
    public function removeMember(User $member, Account $account): void
    {
        if ($member->id === $account->owner_id) {
            throw new \RuntimeException('O dono da conta não pode ser removido.');
        }

        $this->registrar->setPermissionsTeamId($account->id);
        $member->syncRoles([]);
        // A removed member keeps no API access to the former account.
        $member->tokens()->delete();
        $member->account_id = null;
        $member->save();
        $this->registrar->forgetCachedPermissions();
    }

    private function ensurePermissionCatalog(): void
    {
        foreach (RolesAndPermissionsSeeder::permissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    private function defaultName(User $owner): string
    {
        $base = $owner->name !== '' ? $owner->name : $owner->username;

        return "Conta de {$base}";
    }
}
