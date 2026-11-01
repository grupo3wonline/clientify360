<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountLogoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/** Logo of the user's own account (used on PDFs). Never takes an account id from the request. */
final class AccountLogoController extends Controller
{
    public function update(UpdateAccountLogoRequest $request): RedirectResponse
    {
        $account = $request->user()->account;
        abort_if($account === null, 404);

        try {
            $account->addMedia($request->file('logo'))->toMediaCollection('logo');
        } catch (Throwable $e) {
            Log::warning('Account logo could not be stored', ['account_id' => $account->id, 'exception' => $e::class]);

            throw ValidationException::withMessages(['logo' => 'Não foi possível salvar o arquivo. Tente novamente.'])
                ->errorBag('accountLogo');
        }

        return redirect()->to(route('profile.edit').'#logotipo')->with('success', 'Logotipo atualizado.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->account?->clearMediaCollection('logo');

        return redirect()->to(route('profile.edit').'#logotipo')->with('success', 'Logotipo removido.');
    }

    public function show(Request $request): BinaryFileResponse
    {
        $logo = $request->user()->account?->getFirstMedia('logo');

        abort_if($logo === null || !is_file($logo->getPath()), 404);

        return response()->file($logo->getPath(), [
            'Content-Type' => $logo->mime_type,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
