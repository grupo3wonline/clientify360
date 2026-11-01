<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;
use Tests\TestCase;

/**
 * Garante que todas as telas de listagem do painel Filament renderizam
 * para o Dono (que tem todas as permissões).
 */
class PanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    /** Panel resource slug => [permission prefix, AccountRecords parameter]. */
    private const RESOURCES = [
        'clients' => ['clients', 'client'],
        'reminders' => ['clients', 'reminder'],
        'interactions' => ['clients', 'interaction'],
        'tags' => ['clients', 'tag'],
        'deals' => ['deals', 'deal'],
        'activities' => ['deals', 'activity'],
        'products' => ['products', 'product'],
        'quotes' => ['quotes', 'quote'],
        'financial-entries' => ['finance', 'entry'],
        'lead-forms' => ['leads', 'lead_form'],
        'appointments' => ['appointments', 'appointment'],
    ];

    public static function panelPages(): array
    {
        return [
            'dashboard' => ['/app'],
            'clients' => ['/app/clients'],
            'reminders' => ['/app/reminders'],
            'interactions' => ['/app/interactions'],
            'deals' => ['/app/deals'],
            'activities' => ['/app/activities'],
            'tags' => ['/app/tags'],
            'products' => ['/app/products'],
            'quotes' => ['/app/quotes'],
            'finance' => ['/app/financial-entries'],
            'lead-forms' => ['/app/lead-forms'],
        ];
    }

    #[DataProvider('panelPages')]
    public function test_owner_can_open_panel_page(string $url): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->get($url)->assertOk();
    }

    public function test_every_resource_index_create_and_edit_page_answers_owner_and_collaborator(): void
    {
        $owner = Personas::owner('professional');
        $collaborator = Personas::collaborator($owner->account);
        $granted = RolesAndPermissionsSeeder::rolePermissions()[User::ROLE_COLLABORATOR];
        $failures = [];
        $checked = 0;

        foreach ($this->resourcePageRoutes() as $route) {
            preg_match('#^filament\.app\.resources\.([a-z-]+)\.(index|create|edit)$#', (string) $route->getName(), $m);
            $this->assertArrayHasKey($m[1], self::RESOURCES, "Panel resource {$m[1]} is not mapped in the smoke test");
            [$permission, $parameter] = self::RESOURCES[$m[1]];

            auth()->logout();
            $url = $m[2] === 'edit'
                ? route((string) $route->getName(), ['record' => AccountRecords::make($parameter, $owner)->getKey()])
                : route((string) $route->getName());
            $ability = $permission.($m[2] === 'index' ? '.view' : '.manage');

            foreach (['owner' => [$owner, 200], 'collaborator' => [$collaborator, in_array($ability, $granted, true) ? 200 : 403]] as $who => [$user, $expected]) {
                $status = $this->actingAs($user)->get($url)->getStatusCode();
                if ($status !== $expected) {
                    $failures[] = "{$who} {$route->getName()}: {$status}, expected {$expected}";
                }
                $checked++;
            }
        }

        $this->actingAs($collaborator)->get('/app')->assertOk();
        $this->assertSame([], $failures);
        $this->assertGreaterThan(30, $checked);
    }

    /** @return list<RoutingRoute> */
    private function resourcePageRoutes(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $r): bool => (bool) preg_match('#^filament\.app\.resources\.[a-z-]+\.(index|create|edit)$#', (string) $r->getName()))
            ->values()
            ->all();
    }
}
