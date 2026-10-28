<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\ClientsExport;
use App\Services\ClientService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Plano Profissional: o acesso é garantido pelo middleware plan.feature:export. */
final class ExportController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    /** Exporta clientes em XLSX (Laravel Excel). */
    public function clientsExcel(): BinaryFileResponse
    {
        return Excel::download(new ClientsExport, 'clientes_'.now()->format('Y-m-d').'.xlsx');
    }

    public function clients(): StreamedResponse
    {
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
