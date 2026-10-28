<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ApiTokenLimitException;
use App\Http\Requests\StoreApiTokenRequest;
use App\Services\ApiTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** "Tokens de API" section of the profile page. */
final class ApiTokenController extends Controller
{
    public function __construct(
        private readonly ApiTokenService $tokens,
    ) {}

    public function store(StoreApiTokenRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $token = $this->tokens->create($request->user(), $data['name'], $data['abilities'], (int) $data['expires_in_days']);
        } catch (ApiTokenLimitException $e) {
            return back()->withErrors(['name' => $e->getMessage()], 'apiToken')->withInput()->withFragment('tokens-api');
        }

        return redirect()->to(route('profile.edit').'#tokens-api')
            ->with('plain_token', $token->plainTextToken)
            ->with('success', 'Token criado. Copie agora: ele não será exibido novamente.');
    }

    public function destroy(Request $request, string $tokenId): RedirectResponse
    {
        $this->tokens->revoke($request->user(), $tokenId);

        return redirect()->to(route('profile.edit').'#tokens-api')->with('success', 'Token revogado.');
    }
}
