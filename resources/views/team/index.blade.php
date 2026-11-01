<x-app-layout>
    @section('title', 'Equipe')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Equipe']]" />
    </x-slot>

    <x-page-title help-description="Convide colaboradores e defina o papel de cada um." help-example="Convide sua secretária como Colaboradora." help-tip="Colaboradores não veem a assinatura nem a equipe." title="Equipe e colaboradores" icon="fa-users-gear"
        subtitle="Convide pessoas para acessar a conta e defina o papel de cada uma. Dica: o Dono controla tudo; o Gestor opera sem acesso à cobrança; o Colaborador trabalha clientes, funil e orçamentos." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Membros --}}
            <div class="c-card divide-y divide-surface-100 dark:divide-surface-800">
                <div class="p-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold"><i class="fa-solid fa-user-group text-brand-500 mr-2"></i>Membros ({{ $members->count() }})</h3>
                </div>
                @foreach($members as $member)
                    @php($isOwner = $member->id === $account->owner_id)
                    @php($role = $member->getRoleNames()->first())
                    <div class="p-4 flex flex-wrap items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-surface-900 dark:text-white truncate">
                                {{ $member->name }}
                                @if($member->id === auth()->id())<span class="text-[10px] text-surface-400">(você)</span>@endif
                            </p>
                            <p class="text-xs text-surface-500 truncate">{{ $member->email }}</p>
                        </div>

                        @if($isOwner)
                            <span class="c-badge bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 text-xs px-3 py-1">Dono</span>
                        @elseif(auth()->user()->can('account.members'))
                            <form autocomplete="off" action="{{ route('team.updateRole', $member) }}" method="POST" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <select autocomplete="off" name="role" onchange="this.form.submit()" class="c-input py-1.5 text-xs w-36">
                                    @foreach($roles as $r)
                                        <option value="{{ $r }}" @selected($role === $r)>{{ \App\Models\User::ROLE_LABELS[$r] }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <x-confirm-delete :action="route('team.removeMember', $member)" title="Remover colaborador?"
                                text="Ele perderá o acesso à conta imediatamente." />
                        @else
                            <span class="c-badge bg-surface-100 text-surface-600 dark:bg-surface-800 dark:text-surface-300 text-xs px-3 py-1">
                                {{ \App\Models\User::ROLE_LABELS[$role] ?? $role }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Convites pendentes --}}
            @if($invitations->count() > 0)
                <div class="c-card divide-y divide-surface-100 dark:divide-surface-800">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold"><i class="fa-solid fa-paper-plane text-amber-500 mr-2"></i>Convites pendentes ({{ $invitations->count() }})</h3>
                    </div>
                    @foreach($invitations as $invitation)
                        <div class="p-4 flex flex-wrap items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-800 dark:text-surface-200 truncate">{{ $invitation->email }}</p>
                                <p class="text-xs text-surface-500">
                                    {{ \App\Models\User::ROLE_LABELS[$invitation->role] ?? $invitation->role }}
                                    · {{ $invitation->isExpired() ? 'expirado' : 'expira '.$invitation->expires_at->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('register', ['invite' => $invitation->token]) }}" target="_blank"
                               class="text-xs text-brand-600 hover:underline" title="Link do convite">
                                <i class="fa-solid fa-link"></i> link
                            </a>
                            @can('account.members')
                                <x-confirm-delete :action="route('team.revokeInvite', $invitation)" title="Cancelar convite?" text="O link deixará de funcionar." />
                            @endcan
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Convidar --}}
        @can('account.members')
            <div class="c-card p-5 h-fit">
                <h3 class="text-sm font-semibold mb-1"><i class="fa-solid fa-user-plus text-brand-500 mr-2"></i>Convidar colaborador</h3>
                <p class="text-xs text-surface-500 mb-4">Enviaremos um link de cadastro. Ao se registrar, a pessoa entra na conta com o papel escolhido.</p>
                <form autocomplete="off" action="{{ route('team.invite') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="c-label">E-mail</label>
                        <input type="email" name="email" required value="{{ old('email') }}" autocomplete="off" class="c-input" placeholder="colaborador@empresa.com">
                        @error('email')<p class="c-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="c-label">Papel</label>
                        <select autocomplete="off" name="role" class="c-input">
                            @foreach($roles as $r)
                                <option value="{{ $r }}" @selected(old('role') === $r)>{{ \App\Models\User::ROLE_LABELS[$r] }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-surface-400 mt-1">Gestor: tudo exceto cobrança. Colaborador: clientes, funil, orçamentos e leads.</p>
                    </div>
                    <button class="c-btn-primary w-full">Enviar convite</button>
                </form>
            </div>
        @endcan
    </div>
</x-app-layout>
