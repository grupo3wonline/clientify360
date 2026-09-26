<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\ClientDTO;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\Tag;
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
        $tagId = $request->query('tag');
        $clients = $this->service->paginate(15, $search, $tagId);
        $tags = Tag::orderBy('name')->get();

        return view('clients.index', compact('clients', 'search', 'tags', 'tagId'));
    }

    public function create(): View
    {
        return view('clients.create', ['tags' => Tag::orderBy('name')->get()]);
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

        $client->load([
            'tags',
            'deals' => fn ($q) => $q->with('stage:id,name,color')->latest(),
            'quotes' => fn ($q) => $q->limit(10),
            'activities' => fn ($q) => $q->with('deal:id,title')->limit(20),
        ]);

        $finance = [
            'open' => (float) $client->financialEntries()->receivable()->pending()->sum('amount'),
            'overdue' => (float) $client->financialEntries()->receivable()->overdue()->sum('amount'),
            'received' => (float) $client->financialEntries()->receivable()->where('status', 'paid')->sum('paid_amount'),
        ];

        return view('clients.show', compact('client', 'finance'));
    }

    public function edit(string $id): View
    {
        $client = $this->service->findById($id);

        abort_if($client === null, 404);
        $this->authorize('update', $client);

        $client->load('tags');
        $tags = Tag::orderBy('name')->get();

        return view('clients.edit', compact('client', 'tags'));
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
