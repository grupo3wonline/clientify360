<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Reminder;
use App\Support\OwnedRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Histórico de relacionamento com "próximo passo" (crm-master) + tipos/resultado (CrmPlus). */
final class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $type = $this->queryString($request, 'type');
        $clientId = $this->queryString($request, 'client_id');

        $activities = Activity::with(['client:id,name', 'deal:id,title', 'reminder:id,is_completed'])
            ->when(array_key_exists((string) $type, Activity::TYPES), fn ($q) => $q->where('type', $type))
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->latest('happened_at')
            ->paginate(20)
            ->withQueryString();

        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('activities.index', compact('activities', 'clients', 'type', 'clientId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'string', OwnedRule::exists('clients')],
            'deal_id' => ['nullable', 'string', OwnedRule::exists('deals')],
            'type' => ['required', Rule::in(array_keys(Activity::TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'happened_at' => ['nullable', 'date'],
            'outcome' => ['nullable', Rule::in(array_keys(Activity::OUTCOMES))],
            'next_step' => ['nullable', 'string', 'max:1000', 'required_with:next_step_at'],
            'next_step_at' => ['nullable', 'date', 'after:now'],
        ], [
            'client_id.required' => 'Selecione o cliente.',
            'title.required' => 'Descreva a atividade em poucas palavras.',
            'next_step.required_with' => 'Descreva o próximo passo.',
            'next_step_at.after' => 'A data do próximo passo deve ser no futuro.',
        ]);

        DB::transaction(function () use ($data): void {
            $reminderId = null;

            // Próximo passo com data vira lembrete na Agenda → aparece nas Notificações
            if (!empty($data['next_step']) && !empty($data['next_step_at'])) {
                $reminderId = Reminder::create([
                    'client_id' => $data['client_id'],
                    'title' => 'Próximo passo: '.mb_strimwidth($data['next_step'], 0, 200, '…'),
                    'description' => $data['title'],
                    'remind_at' => $data['next_step_at'],
                ])->id;
            }

            Activity::create([
                ...$data,
                'happened_at' => $data['happened_at'] ?? now(),
                'reminder_id' => $reminderId,
            ]);
        });

        return back()->with('success', 'Atividade registrada no histórico!');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return back()->with('success', 'Atividade removida.');
    }
}
