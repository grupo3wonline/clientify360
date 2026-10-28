<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ExportClientsXlsxJob;
use App\Models\Client;
use App\Support\PrivacyLog;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Plano Profissional: o acesso é garantido pelo middleware plan.feature:export. */
final class ExportController extends Controller
{
    /** Queues the XLSX export; the link arrives by e-mail. */
    public function clientsExcel(Request $request): RedirectResponse
    {
        $user = $request->user();

        ExportClientsXlsxJob::dispatch($user->id, (string) $user->account_id);

        return back()->with('success', "Sua planilha está sendo gerada. Enviaremos o link de download para {$user->email} em alguns minutos.");
    }

    /** Signed link from ExportReadyNotification. The file must belong to the user's account. */
    public function download(Request $request): StreamedResponse
    {
        try {
            $path = decrypt((string) $request->query('path'));
        } catch (DecryptException) {
            abort(404);
        }

        $prefix = ExportClientsXlsxJob::directoryFor((string) $request->user()->account_id);

        abort_unless(is_string($path) && str_starts_with($path, $prefix) && !str_contains($path, '..'), 404);

        $disk = Storage::disk(ExportClientsXlsxJob::DISK);
        abort_unless($disk->exists($path), 404, 'O arquivo expirou ou não existe mais.');

        return $disk->download($path, basename($path));
    }

    public function clients(Request $request): StreamedResponse
    {
        PrivacyLog::record('clients.exported', 'Exportação de clientes (CSV)', $request->user(), null, ['format' => 'csv']);

        $fileName = 'clientes_'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            // BOM for UTF-8 Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nome',
                'E-mail',
                'Telefone',
                'Documento',
                'Endereço',
                'Cidade',
                'Estado',
                'CEP',
                'Observações',
                'Cadastrado em',
            ], ';');

            // Authenticated request: the `owned` scope limits rows to the user's account.
            foreach (Client::query()->orderBy('name')->lazy(500) as $client) {
                fputcsv($handle, [
                    $client->name,
                    $client->email ?? '',
                    $client->phone ?? '',
                    $client->document ?? '',
                    $client->address ?? '',
                    $client->city ?? '',
                    $client->state ?? '',
                    $client->zip_code ?? '',
                    $client->notes ?? '',
                    $client->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
