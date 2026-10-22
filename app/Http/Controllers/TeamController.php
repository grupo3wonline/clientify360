<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AccountInvitation;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestão de equipe da conta: membros, papéis e convites.
 * Protegido pela permissão `account.members` (Dono e Gestor).
 */
final class TeamController extends Controller
{
    public function __construct(
        private readonly AccountService $accounts
    ) {}

    public function index(Request $request): View
    {
        $account = $request->user()->account;

        $members = User::where('account_id', $account->id)
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$account->owner_id])
            ->orderBy('name')
            ->get();

        $invitations = AccountInvitation::where('account_id', $account->id)
            ->whereNull('accepted_at')
            ->latest()
            ->get();

        return view('team.index', [
            'account' => $account,
            'members' => $members,
            'invitations' => $invitations,
            'roles' => [User::ROLE_MANAGER, User::ROLE_COLLABORATOR],
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $this->authorizeMembers($request);
        $account = $request->user()->account;

        $validated = $request->validate([
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->where('account_id', $account->id),
            ],
            'role' => ['required', Rule::in([User::ROLE_MANAGER, User::ROLE_COLLABORATOR])],
        ], [
            'email.required' => 'Informe o e-mail do colaborador.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já pertence a um membro da conta.',
            'role.required' => 'Selecione o papel do colaborador.',
            'role.in' => 'Papel inválido.',
        ]);

        $this->accounts->invite($account, $validated['email'], $validated['role'], $request->user());

        return redirect()->route('team.index')
            ->with('success', 'Convite enviado para '.$validated['email'].'.');
    }

    public function updateRole(Request $request, User $member): RedirectResponse
    {
        $this->authorizeMembers($request);
        $account = $request->user()->account;

        abort_unless($member->account_id === $account->id, 404);
        abort_if($member->id === $account->owner_id, 403, 'O papel do dono não pode ser alterado.');

        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_MANAGER, User::ROLE_COLLABORATOR])],
        ]);

        $this->accounts->assignRole($member, $validated['role'], $account);

        return redirect()->route('team.index')->with('success', 'Papel atualizado.');
    }

    public function removeMember(Request $request, User $member): RedirectResponse
    {
        $this->authorizeMembers($request);
        $account = $request->user()->account;

        abort_unless($member->account_id === $account->id, 404);
        abort_if($member->id === $account->owner_id, 403, 'O dono da conta não pode ser removido.');

        $this->accounts->removeMember($member, $account);

        return redirect()->route('team.index')->with('success', 'Colaborador removido da conta.');
    }

    public function revokeInvite(Request $request, AccountInvitation $invitation): RedirectResponse
    {
        $this->authorizeMembers($request);

        abort_unless($invitation->account_id === $request->user()->account_id, 404);

        $invitation->delete();

        return redirect()->route('team.index')->with('success', 'Convite cancelado.');
    }

    private function authorizeMembers(Request $request): void
    {
        abort_unless($request->user()->can('account.members'), 403);
    }
}
