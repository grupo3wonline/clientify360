<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Garante que todas as telas de listagem do painel Filament renderizam
 * para o Dono (que tem todas as permissões).
 */
class PanelSmokeTest extends TestCase
{
    use RefreshDatabase;

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
}
