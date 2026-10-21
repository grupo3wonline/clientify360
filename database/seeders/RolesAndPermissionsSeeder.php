<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Papéis são escopados por conta (teams = true no Spatie). As permissões,
 * porém, são globais. Este seeder cria o catálogo de permissões uma vez.
 * Os papéis por conta são criados pelo AccountService ao provisionar a conta.
 */
final class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::permissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    /**
     * Catálogo de permissões do sistema.
     *
     * @return list<string>
     */
    public static function permissions(): array
    {
        return [
            // Dados de negócio
            'clients.view', 'clients.manage',
            'deals.view', 'deals.manage',
            'products.view', 'products.manage',
            'quotes.view', 'quotes.manage',
            'finance.view', 'finance.manage',
            'leads.view', 'leads.manage',
            'appointments.view', 'appointments.manage',
            'service_orders.view', 'service_orders.manage',
            'reports.view',
            // Administração da conta
            'account.settings', 'account.members', 'account.billing',
        ];
    }

    /**
     * Permissões por papel. É a fonte de verdade usada ao criar os papéis
     * de cada conta.
     *
     * @return array<string, list<string>>
     */
    public static function rolePermissions(): array
    {
        $all = self::permissions();

        $manager = array_values(array_filter(
            $all,
            fn (string $p): bool => $p !== 'account.billing'
        ));

        $collaborator = [
            'clients.view', 'clients.manage',
            'deals.view', 'deals.manage',
            'products.view',
            'quotes.view', 'quotes.manage',
            'leads.view', 'leads.manage',
            'appointments.view', 'appointments.manage',
            'service_orders.view', 'service_orders.manage',
            'reports.view',
        ];

        return [
            User::ROLE_OWNER => $all,
            User::ROLE_MANAGER => $manager,
            User::ROLE_COLLABORATOR => $collaborator,
        ];
    }
}
