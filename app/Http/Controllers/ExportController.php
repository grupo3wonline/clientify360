<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\ClientsExport;
use App\Services\ClientService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly SubscriptionService $subscriptionService
    ) {}

    /** Exporta clientes em XLSX (Laravel Excel), plano Profissional. */
    public function clientsExcel(Request $request): BinaryFileResponse
    {
        abort_unless($this->subscriptionService->isProfessional($request->user()), 403, 'Exportação disponível apenas no plano Profissional.');

        return Excel::download(new ClientsExport, 'clientes_'.now()->format('Y-m-d').'.xlsx');
    }

    public function clients(Request $request): StreamedResponse
    {
        $user = $request->user();

        if (!$this->subscriptionService->isProfessional($user)) {
            abort(403, 'Exportação disponível apenas no plano Profissional.');
        }

        $clients = $this->clientService->findAll();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="clientes_'.now()->format('Y-m-d').'.csv"',
        ];

        return response()->streamDownload(function () use ($clients): void {
            $handle = fopen('php://output', 'w');

            // BOM for UTF-8 Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
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

            // Data rows
            foreach ($clients as $client) {
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
        }, 'clientes_'.now()->format('Y-m-d').'.csv', $headers);
    }
}
