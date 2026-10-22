<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AccountInvitation;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

final class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly AccountService $accounts
    ) {}

    public function create(Request $request): View
    {
        // Pré-carrega um convite válido (quando o cadastro vem de um link de convite).
        $invitation = $this->resolveInvitation($request->query('invite'));

        return view('auth.register', [
            'invitation' => $invitation,
            'inviteToken' => $invitation?->token,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'alpha_dash'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'lgpd_consent' => ['required', 'accepted'],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'username.required' => 'O nome de usuário é obrigatório.',
            'username.unique' => 'Este nome de usuário já está em uso.',
            'username.alpha_dash' => 'O nome de usuário deve conter apenas letras, números, traços e underscores.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'lgpd_consent.required' => 'Você deve aceitar a Política de Privacidade.',
            'lgpd_consent.accepted' => 'Você deve aceitar a Política de Privacidade.',
        ]);

        $invitation = $this->resolveInvitation($request->input('invite_token'));

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'lgpd_consent' => true,
            'lgpd_consent_at' => now(),
        ]);

        if ($invitation !== null) {
            // Cadastro via convite: entra na conta existente com o papel convidado.
            $this->accounts->addMember($user, $invitation->account, $invitation->role);
            $invitation->update(['accepted_at' => now()]);
        } else {
            // Cadastro normal: cria a própria conta e vira o dono.
            $this->accounts->provisionForOwner($user);
        }

        event(new Registered($user));

        Auth::login($user);

        // Usuário recém-criado ainda não verificou o e-mail: o painel exige
        // verificação, então enviamos ao aviso de verificação do Breeze.
        return redirect()->route('verification.notice');
    }

    /** Resolve um convite pendente e válido pelo token, se houver. */
    private function resolveInvitation(?string $token): ?AccountInvitation
    {
        if ($token === null || $token === '') {
            return null;
        }

        $invitation = AccountInvitation::with('account')->where('token', $token)->first();

        return $invitation !== null && $invitation->isPending() ? $invitation : null;
    }
}
