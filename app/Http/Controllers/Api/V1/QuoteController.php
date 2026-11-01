<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListRequest;
use App\Http\Resources\V1\QuoteResource;
use App\Models\Quote;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

final class QuoteController extends Controller
{
    #[OA\Get(
        path: '/api/v1/quotes',
        operationId: 'listQuotes',
        description: 'Lista paginada dos orçamentos da conta (mais recentes primeiro). O parâmetro search filtra por número ou título. Habilidade do token: quotes:read.',
        summary: 'Listar orçamentos',
        security: [['sanctum' => []]],
        tags: ['Orçamentos'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/Search'),
            new OA\Parameter(ref: '#/components/parameters/PerPage'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Quote')),
                new OA\Property(property: 'links', type: 'object'),
                new OA\Property(property: 'meta', type: 'object'),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/Unprocessable', response: 422),
        ],
    )]
    public function index(ListRequest $request): AnonymousResourceCollection
    {
        $query = $request->toData();
        $term = $query->search === null ? null : str_replace(['%', '_'], ['\%', '\_'], $query->search);

        $quotes = Quote::query()
            ->with('client:id,name')
            ->when($term !== null, fn ($q) => $q->where(fn ($w) => $w
                ->where('number', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%")))
            ->latest()
            ->paginate($query->perPage)
            ->withQueryString();

        return QuoteResource::collection($quotes);
    }

    #[OA\Get(
        path: '/api/v1/quotes/{quote}',
        operationId: 'showQuote',
        description: 'Orçamento com cliente e itens. Habilidade do token: quotes:read.',
        summary: 'Detalhar orçamento',
        security: [['sanctum' => []]],
        tags: ['Orçamentos'],
        parameters: [new OA\Parameter(name: 'quote', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Orçamento', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Quote'),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
        ],
    )]
    public function show(Quote $quote): QuoteResource
    {
        $this->authorize('view', $quote);

        return new QuoteResource($quote->load(['client:id,name', 'items']));
    }
}
