<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exports\ClientsExport;
use App\Models\User;
use App\Notifications\ExportFailedNotification;
use App\Notifications\ExportReadyNotification;
use App\Support\PrivacyLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/** Builds the clients XLSX of one account on the private disk and e-mails a signed link. */
final class ExportClientsXlsxJob implements ShouldQueue
{
    use Queueable;

    public const DISK = 'local';

    public const LINK_TTL_HOURS = 24;

    public int $tries = 2;

    public function __construct(
        public readonly string $userId,
        public readonly string $accountId,
    ) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [120];
    }

    public static function directoryFor(string $accountId): string
    {
        return "exports/{$accountId}/";
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if ($user === null) {
            return;
        }

        $path = self::directoryFor($this->accountId).'clientes-'.now()->format('Ymd-His').'-'.Str::random(8).'.xlsx';

        Excel::store(new ClientsExport($this->accountId), $path, self::DISK);

        PrivacyLog::record('clients.exported', 'Exportação de clientes (XLSX)', $user, null, ['format' => 'xlsx']);

        $url = URL::temporarySignedRoute('export.download', now()->addHours(self::LINK_TTL_HOURS), ['path' => encrypt($path)]);

        $user->notify(new ExportReadyNotification($url));
    }

    public function failed(?Throwable $e): void
    {
        Log::error('Clients XLSX export failed', ['account_id' => $this->accountId, 'error' => $e?->getMessage()]);

        User::find($this->userId)?->notify(new ExportFailedNotification);
    }
}
