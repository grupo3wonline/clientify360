<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Interaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InteractionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $query = Interaction::with('client')->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $interactions = $query->paginate(15);
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $counts = [
            'all' => Interaction::count(),
            'open' => Interaction::where('status', 'open')->count(),
            'in_progress' => Interaction::where('status', 'in_progress')->count(),
            'resolved' => Interaction::where('status', 'resolved')->count(),
        ];

        return view('interactions.index', compact('interactions', 'clients', 'status', 'counts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'string', 'exists:clients,id'],
            'type' => ['required', 'string', 'in:support,sales,follow_up,complaint,other'],
            'channel' => ['required', 'string', 'in:phone,email,whatsapp,meeting,other'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'client_id.required' => 'Selecione um cliente.',
            'client_id.exists' => 'Cliente inválido.',
            'type.required' => 'Selecione o tipo de atendimento.',
            'channel.required' => 'Selecione o canal de atendimento.',
            'subject.required' => 'O assunto é obrigatório.',
            'subject.max' => 'O assunto não pode ter mais de 255 caracteres.',
        ]);

        Interaction::create($validated);

        return redirect()->route('interactions.index')
            ->with('success', 'Atendimento registrado com sucesso!');
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $interaction = Interaction::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'resolved' || $validated['status'] === 'closed') {
            $data['resolved_at'] = now();
            $data['resolution_notes'] = $validated['resolution_notes'] ?? null;
        }

        $interaction->update($data);

        return redirect()->route('interactions.index')
            ->with('success', 'Status atualizado com sucesso!');
    }

    public function destroy(string $id): RedirectResponse
    {
        $interaction = Interaction::findOrFail($id);
        $interaction->delete();

        return redirect()->route('interactions.index')
            ->with('success', 'Atendimento excluído com sucesso!');
    }
}
