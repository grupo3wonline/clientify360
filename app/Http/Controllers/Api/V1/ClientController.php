<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateClientAction;
use App\DTOs\ClientDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListRequest;
use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Http\Resources\V1\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use App\Services\SearchService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

final class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clients,
        private readonly SearchService $search,
    ) {}

    #[OA\Get(
        path: '/api/v1/clients',
        operationId: 'listClients',
        description: 'Lista paginada dos clientes da conta. Habilidade do token: clients:read.',
        summary: 'Listar clientes',
        security: [['sanctum' => []]],
        tags: ['Clientes'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/Search'),
            new OA\Parameter(ref: '#/components/parameters/PerPage'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Client')),
                new OA\Property(property: 'links', type: 'object'),
                new OA\Property(property: 'meta', type: 'object'),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/Unprocessable', response: 422),
            new OA\Response(ref: '#/components/responses/TooManyRequests', response: 429),
        ],
    )]
    public function index(ListRequest $request): AnonymousResourceCollection
    {
        $query = $request->toData();

        $clients = $query->search !== null
            ? $this->search->clients($request->user(), $query->search, $query->perPage)
            : Client::query()->with('tags')->latest()->paginate($query->perPage);

        return ClientResource::collection($clients->withQueryString());
    }

    #[OA\Get(
        path: '/api/v1/clients/{client}',
        operationId: 'showClient',
        description: 'Habilidade do token: clients:read.',
        summary: 'Detalhar cliente',
        security: [['sanctum' => []]],
        tags: ['Clientes'],
        parameters: [new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Cliente', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Client'),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
        ],
    )]
    public function show(Client $client): ClientResource
    {
        $this->authorize('view', $client);

        return new ClientResource($client->load('tags'));
    }

    #[OA\Post(
        path: '/api/v1/clients',
        operationId: 'storeClient',
        description: 'Habilidade do token: clients:write.',
        summary: 'Cadastrar cliente',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ClientInput')),
        tags: ['Clientes'],
        responses: [
            new OA\Response(response: 201, description: 'Cliente criado', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Client'),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/Unprocessable', response: 422),
        ],
    )]
    public function store(StoreClientRequest $request, CreateClientAction $create, SubscriptionService $plans): JsonResponse
    {
        $user = $request->user();

        // Defense in depth: today Básico is stopped by plan.feature:api and
        // Profissional has no client limit.
        if (!$plans->canCreate($user, 'clients')) {
            return response()->json(['message' => $plans->limitMessage($user, 'clients')], 403);
        }

        $client = $create->execute(ClientDTO::fromArray($request->validated()));

        return (new ClientResource($client->load('tags')))->response()->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/v1/clients/{client}',
        operationId: 'updateClient',
        description: 'Habilidade do token: clients:write.',
        summary: 'Atualizar cliente',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ClientInput')),
        tags: ['Clientes'],
        parameters: [new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Cliente atualizado', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Client'),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
            new OA\Response(ref: '#/components/responses/Unprocessable', response: 422),
        ],
    )]
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $this->authorize('update', $client);

        $updated = $this->clients->update($client->id, ClientDTO::fromArray($request->validated()));

        return new ClientResource($updated->load('tags'));
    }

    #[OA\Delete(
        path: '/api/v1/clients/{client}',
        operationId: 'destroyClient',
        description: 'Exclusão lógica (o registro fica na lixeira). Habilidade do token: clients:write.',
        summary: 'Excluir cliente',
        security: [['sanctum' => []]],
        tags: ['Clientes'],
        parameters: [new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 204, description: 'Excluído'),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
        ],
    )]
    public function destroy(Client $client): Response
    {
        $this->authorize('delete', $client);

        $this->clients->delete($client->id);

        return response()->noContent();
    }
}
