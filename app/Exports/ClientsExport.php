<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Client;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exportação de clientes em XLSX. Roda na fila (sem usuário autenticado), por
 * isso filtra explicitamente pela conta recebida no construtor.
 */
final class ClientsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $accountId,
    ) {}

    /**
     * @return Collection<int, Client>
     */
    public function collection(): Collection
    {
        return Client::withoutGlobalScope('owned')
            ->where('account_id', $this->accountId)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Nome', 'E-mail', 'Telefone', 'Documento', 'Endereço',
            'Cidade', 'UF', 'CEP', 'Origem', 'Observações', 'Cadastrado em',
        ];
    }

    /**
     * @param  Client  $client
     * @return list<string>
     */
    public function map($client): array
    {
        return [
            $client->name,
            $client->email ?? '',
            $client->phone ?? '',
            $client->document ?? '',
            $client->address ?? '',
            $client->city ?? '',
            $client->state ?? '',
            $client->zip_code ?? '',
            $client->sourceLabel() ?? '',
            $client->notes ?? '',
            $client->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
