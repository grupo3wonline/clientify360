<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientAttachmentRequest;
use App\Models\Client;
use App\Services\ClientAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Client attachments: {client} is bound through the account scope, {media} is scoped by the client. */
final class ClientAttachmentController extends Controller
{
    public function __construct(
        private readonly ClientAttachmentService $attachments,
    ) {}

    public function store(StoreClientAttachmentRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $this->attachments->store($client, $request->file('file'), $request->user());

        return redirect()->to(route('clients.show', $client).'#anexos')->with('success', 'Anexo enviado com sucesso!');
    }

    public function show(Request $request, Client $client, string $media): BinaryFileResponse
    {
        $this->authorize('view', $client);

        return $this->attachments->download($client, $this->attachments->find($client, $media), $request->user());
    }

    public function destroy(Client $client, string $media): RedirectResponse
    {
        $this->authorize('update', $client);

        $this->attachments->delete($this->attachments->find($client, $media));

        return redirect()->to(route('clients.show', $client).'#anexos')->with('success', 'Anexo excluído.');
    }
}
