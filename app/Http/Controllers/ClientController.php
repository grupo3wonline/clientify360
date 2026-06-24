<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\ClientDTO;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $service
    ) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $clients = $this->service->paginate(15, $search);

        return view('clients.index', compact('clients', 'search'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $dto = ClientDTO::fromArray($request->validated());
        $this->service->create($dto);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(string $id): View
    {
        $client = $this->service->findById($id);

        abort_if($client === null, 404);
        $this->authorize('view', $client);

        return view('clients.show', compact('client'));
    }

    public function edit(string $id): View
    {
        $client = $this->service->findById($id);

        abort_if($client === null, 404);
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, string $id): RedirectResponse
    {
        $client = $this->service->findById($id);

        abort_if($client === null, 404);
        $this->authorize('update', $client);

        $dto = ClientDTO::fromArray($request->validated());
        $this->service->update($id, $dto);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(string $id): RedirectResponse
    {
        $client = $this->service->findById($id);

        abort_if($client === null, 404);
        $this->authorize('delete', $client);

        $this->service->delete($id);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }
}
