<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use OpenApi\Attributes as OA;

/** Holds the document-level OpenAPI attributes of the API v1. */
#[OA\Info(
    version: '1.0.0',
    description: 'API REST do Clientify360 (v1). Autenticação por token Bearer (Sanctum) gerado em Meu perfil → Tokens de API. Disponível no plano Profissional. Cada token só acessa os dados da própria conta e somente as permissões escolhidas na criação.',
    title: 'Clientify360 API',
)]
#[OA\Server(url: '/', description: 'Servidor atual')]
#[OA\SecurityScheme(securityScheme: 'sanctum', type: 'http', scheme: 'bearer')]
#[OA\Tag(name: 'Conta', description: 'Dados do usuário do token')]
#[OA\Tag(name: 'Clientes', description: 'Cadastro de clientes')]
#[OA\Tag(name: 'Produtos', description: 'Catálogo de produtos e serviços (leitura)')]
#[OA\Tag(name: 'Orçamentos', description: 'Orçamentos (leitura)')]
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: 'Error',
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Registro não encontrado.')],
        ),
        new OA\Schema(
            schema: 'ValidationError',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'O nome é obrigatório.'),
                new OA\Property(property: 'errors', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string'))),
            ],
        ),
        new OA\Schema(
            schema: 'Client',
            properties: [
                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                new OA\Property(property: 'name', type: 'string', example: 'Maria Souza'),
                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'maria@exemplo.com'),
                new OA\Property(property: 'phone', type: 'string', nullable: true),
                new OA\Property(property: 'document', type: 'string', nullable: true),
                new OA\Property(property: 'person_type', type: 'string', enum: ['individual', 'company']),
                new OA\Property(property: 'address', type: 'string', nullable: true),
                new OA\Property(property: 'city', type: 'string', nullable: true),
                new OA\Property(property: 'state', type: 'string', nullable: true),
                new OA\Property(property: 'zip_code', type: 'string', nullable: true),
                new OA\Property(property: 'notes', type: 'string', nullable: true),
                new OA\Property(property: 'source', type: 'string', nullable: true),
                new OA\Property(property: 'tags', type: 'array', items: new OA\Items(ref: '#/components/schemas/Tag')),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
        ),
        new OA\Schema(
            schema: 'ClientInput',
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255),
                new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                new OA\Property(property: 'phone', type: 'string', maxLength: 20, nullable: true),
                new OA\Property(property: 'person_type', type: 'string', enum: ['individual', 'company']),
                new OA\Property(property: 'document', type: 'string', description: 'CPF ou CNPJ válido', nullable: true),
                new OA\Property(property: 'address', type: 'string', nullable: true),
                new OA\Property(property: 'city', type: 'string', nullable: true),
                new OA\Property(property: 'state', type: 'string', maxLength: 2, nullable: true),
                new OA\Property(property: 'zip_code', type: 'string', maxLength: 10, nullable: true),
                new OA\Property(property: 'notes', type: 'string', maxLength: 1000, nullable: true),
                new OA\Property(property: 'tag_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
            ],
        ),
        new OA\Schema(
            schema: 'Tag',
            properties: [
                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'color', type: 'string', example: '#4f46e5'),
            ],
        ),
        new OA\Schema(
            schema: 'Product',
            properties: [
                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                new OA\Property(property: 'type', type: 'string', enum: ['product', 'service']),
                new OA\Property(property: 'sku', type: 'string', nullable: true),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'unit', type: 'string', example: 'un'),
                new OA\Property(property: 'price', type: 'string', example: '29.90'),
                new OA\Property(property: 'promo_price', type: 'string', nullable: true),
                new OA\Property(property: 'track_stock', type: 'boolean'),
                new OA\Property(property: 'stock', type: 'string', nullable: true),
                new OA\Property(property: 'is_active', type: 'boolean'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
        ),
        new OA\Schema(
            schema: 'Quote',
            properties: [
                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                new OA\Property(property: 'number', type: 'string', example: 'ORC-2026-0001'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'status', type: 'string', enum: ['draft', 'sent', 'approved', 'rejected', 'expired']),
                new OA\Property(property: 'client', properties: [
                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'name', type: 'string'),
                ], type: 'object', nullable: true),
                new OA\Property(property: 'valid_until', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'subtotal', type: 'string', example: '100.00'),
                new OA\Property(property: 'discount', type: 'string', example: '0.00'),
                new OA\Property(property: 'total', type: 'string', example: '100.00'),
                new OA\Property(property: 'payment_terms', type: 'string', nullable: true),
                new OA\Property(property: 'notes', type: 'string', nullable: true),
                new OA\Property(property: 'approved_at', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'product_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'quantity', type: 'string', example: '1.000'),
                    new OA\Property(property: 'unit_price', type: 'string', example: '100.00'),
                    new OA\Property(property: 'total', type: 'string', example: '100.00'),
                    new OA\Property(property: 'position', type: 'integer'),
                ], type: 'object')),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
        ),
    ],
    responses: [
        new OA\Response(response: 'Unauthenticated', description: 'Token ausente, inválido ou expirado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        new OA\Response(response: 'Forbidden', description: 'Sem permissão, token sem a habilidade ou plano sem acesso à API', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        new OA\Response(response: 'NotFound', description: 'Registro não encontrado (ou de outra conta)', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        new OA\Response(response: 'Unprocessable', description: 'Erro de validação', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        new OA\Response(response: 'TooManyRequests', description: 'Limite de requisições por minuto atingido', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
    ],
    parameters: [
        new OA\Parameter(parameter: 'PerPage', name: 'per_page', description: 'Itens por página (1 a 100, padrão 15)', in: 'query', required: false, schema: new OA\Schema(type: 'integer', maximum: 100, minimum: 1)),
        new OA\Parameter(parameter: 'Page', name: 'page', description: 'Página', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
        new OA\Parameter(parameter: 'Search', name: 'search', description: 'Termo de busca (até 100 caracteres)', in: 'query', required: false, schema: new OA\Schema(type: 'string', maxLength: 100)),
    ],
)]
final class OpenApiSpec {}
