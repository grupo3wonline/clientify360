<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class MeController extends Controller
{
    #[OA\Get(
        path: '/api/v1/me',
        operationId: 'me',
        description: 'Usuário dono do token, a conta, o plano e as habilidades do token.',
        summary: 'Usuário do token',
        security: [['sanctum' => []]],
        tags: ['Conta'],
        responses: [
            new OA\Response(response: 200, description: 'Usuário', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', properties: [
                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'username', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'account', properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'name', type: 'string'),
                    ], type: 'object'),
                    new OA\Property(property: 'plan', type: 'string', example: 'professional'),
                    new OA\Property(property: 'abilities', type: 'array', items: new OA\Items(type: 'string')),
                ], type: 'object'),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
        ],
    )]
    public function __invoke(Request $request, SubscriptionService $plans): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        return response()->json(['data' => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'account' => [
                'id' => $user->account?->id,
                'name' => $user->account?->name,
            ],
            'plan' => $plans->currentPlan($user)->slug,
            'abilities' => array_values($token->abilities ?? []),
        ]]);
    }
}
