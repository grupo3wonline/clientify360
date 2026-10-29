<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Support\ApiAbilities;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Data for the local load tests (tests/Load). Runs only on the disposable
 * SQLite database created by tests/Load/prepare.php, never on a server DB.
 */
final class LoadTestSeeder extends Seeder
{
    public const MARKER = 'LOADTEST-MARKER';

    public const CREDENTIALS_PATH = 'app/private/loadtest-credentials.json';

    public function run(): void
    {
        if (config('database.default') !== 'sqlite') {
            throw new RuntimeException('LoadTestSeeder runs only on the disposable SQLite load-test database.');
        }

        $this->call(RolesAndPermissionsSeeder::class);

        $password = Str::password(24);
        $user = User::factory()->create([
            'name' => 'Load Test',
            'username' => 'loadtest',
            'email' => 'loadtest@clientify360.test',
            'password' => Hash::make($password),
        ]);
        $account = $user->account;
        $account->forceFill(['stripe_id' => 'cus_loadtest'])->save();
        $account->subscriptions()->create([
            'type' => 'default', 'stripe_id' => 'sub_loadtest', 'stripe_status' => 'active',
            'stripe_price' => 'price_loadtest', 'quantity' => 1,
        ]);

        $owned = ['account_id' => $account->id, 'user_id' => $user->id];
        Client::factory()->create($owned + ['name' => self::MARKER]);
        Client::factory()->count(199)->create($owned);
        Product::factory()->count(50)->create($owned + ['is_active' => true]);

        $token = $user->createToken('loadtest', [ApiAbilities::CLIENTS_READ], now()->addDay())->plainTextToken;

        File::ensureDirectoryExists(dirname(storage_path(self::CREDENTIALS_PATH)));
        File::put(storage_path(self::CREDENTIALS_PATH), (string) json_encode([
            'username' => $user->username,
            'email' => $user->email,
            'password' => $password,
            'token' => $token,
        ], JSON_PRETTY_PRINT));
    }
}
