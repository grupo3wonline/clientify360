<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Reminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReminderController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'upcoming');

        $query = Reminder::with('client')->orderBy('remind_at');

        if ($filter === 'completed') {
            $query->where('is_completed', true)->orderByDesc('completed_at');
        } elseif ($filter === 'overdue') {
            $query->where('is_completed', false)->where('remind_at', '<', now());
        } else {
            $query->where('is_completed', false)->where('remind_at', '>=', now());
        }

        $reminders = $query->paginate(15);
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $counts = [
            'upcoming' => Reminder::where('is_completed', false)->where('remind_at', '>=', now())->count(),
            'overdue' => Reminder::where('is_completed', false)->where('remind_at', '<', now())->count(),
            'completed' => Reminder::where('is_completed', true)->count(),
        ];

        return view('reminders.index', compact('reminders', 'clients', 'filter', 'counts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'client_id' => ['nullable', 'string', 'exists:clients,id'],
            'remind_at' => ['required', 'date', 'after:now'],
        ], [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'remind_at.required' => 'A data do lembrete é obrigatória.',
            'remind_at.after' => 'A data deve ser no futuro.',
            'client_id.exists' => 'Cliente inválido.',
        ]);

        Reminder::create($validated);

        return redirect()->route('reminders.index')
            ->with('success', 'Lembrete criado com sucesso!');
    }

    public function complete(string $id): RedirectResponse
    {
        $reminder = Reminder::findOrFail($id);

        $reminder->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        return redirect()->route('reminders.index')
            ->with('success', 'Lembrete concluído!');
    }

    public function destroy(string $id): RedirectResponse
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->delete();

        return redirect()->route('reminders.index')
            ->with('success', 'Lembrete excluído com sucesso!');
    }
}
