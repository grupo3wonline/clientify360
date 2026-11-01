<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListRequest;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Services\SearchService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

final class ProductController extends Controller
{
    public function __construct(
        private readonly SearchService $search,
    ) {}

    #[OA\Get(
        path: '/api/v1/products',
        operationId: 'listProducts',
        description: 'Lista paginada dos produtos e serviços da conta. Habilidade do token: products:read.',
        summary: 'Listar produtos',
        security: [['sanctum' => []]],
        tags: ['Produtos'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/Search'),
            new OA\Parameter(ref: '#/components/parameters/PerPage'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
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

        $products = $query->search !== null
            ? $this->search->products($request->user(), $query->search, $query->perPage)
            : Product::query()->orderBy('name')->paginate($query->perPage);

        return ProductResource::collection($products->withQueryString());
    }

    #[OA\Get(
        path: '/api/v1/products/{product}',
        operationId: 'showProduct',
        description: 'Habilidade do token: products:read.',
        summary: 'Detalhar produto',
        security: [['sanctum' => []]],
        tags: ['Produtos'],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Produto', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Product'),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
        ],
    )]
    public function show(Product $product): ProductResource
    {
        $this->authorize('view', $product);

        return new ProductResource($product);
    }
}
