<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Support\OwnedRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Funil de vendas (Kanban) e oportunidades — portado do CrmController do CrmPlus. */
final class DealController extends Controller
{
    public function index(Request $request): View
    {
        PipelineStage::ensureDefaultsFor((string) auth()->id());

        $stages = PipelineStage::ordered()->get();
        $search = trim((string) $request->query('search', ''));

        $deals = Deal::open()
            ->with('client:id,name')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))))
            ->orderByDesc('value')
            ->get()
            ->groupBy('pipeline_stage_id');

        $open = Deal::open()->get(['value', 'probability']);
        $metrics = [
            'open_count' => $open->count(),
            'open_value' => (float) $open->sum('value'),
            'weighted_value' => (float) $open->sum(fn ($d) => $d->weightedValue()),
            'won_month_count' => Deal::won()->where('closed_at', '>=', now()->startOfMonth())->count(),
            'won_month_value' => (float) Deal::won()->where('closed_at', '>=', now()->startOfMonth())->sum('value'),
            'conversion' => $this->conversionRate(),
        ];

        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('deals.index', compact('stages', 'deals', 'metrics', 'clients', 'search'));
    }

    /** Lista (inclui ganhas/perdidas) — complemento do Kanban, que mostra só as abertas. */
    public function list(Request $request): View
    {
        $status = $request->query('status');

        $deals = Deal::with(['client:id,name', 'stage:id,name,color'])
            ->when(in_array($status, array_keys(Deal::STATUS_LABELS), true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('deals.list', compact('deals', 'status'));
    }

    public function show(Deal $deal): View
    {
        $deal->load(['client', 'stage', 'quotes', 'activities' => fn ($q) => $q->latest('happened_at')]);
        $stages = PipelineStage::ordered()->get();

        return view('deals.show', compact('deal', 'stages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $stage = isset($data['pipeline_stage_id']) ? PipelineStage::find($data['pipeline_stage_id']) : PipelineStage::ordered()->first();

        $deal = Deal::create($data + [
            'pipeline_stage_id' => $stage?->id,
            'probability' => $stage?->probability ?? 50,
            'status' => Deal::STATUS_OPEN,
        ]);

        return redirect()->to($request->input('redirect_to') === 'client'
                ? route('clients.show', $deal->client_id)
                : route('deals.index'))
            ->with('success', 'Oportunidade criada com sucesso!');
    }

    public function update(Request $request, Deal $deal): RedirectResponse
    {
        $data = $this->validated($request);
        $data['probability'] = (int) $request->input('probability', $deal->probability);
        $deal->update($data);

        return redirect()->route('deals.show', $deal)->with('success', 'Oportunidade atualizada!');
    }

    /** Arrastar card no Kanban (fetch/JSON) ou select de etapa (form). */
    public function move(Request $request, Deal $deal): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'pipeline_stage_id' => ['required', 'string', OwnedRule::exists('pipeline_stages', false)],
        ]);

        $stage = PipelineStage::findOrFail($validated['pipeline_stage_id']);
        $deal->update(['pipeline_stage_id' => $stage->id, 'probability' => $stage->probability]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'stage' => $stage->name, 'probability' => $stage->probability]);
        }

        return back()->with('success', "Oportunidade movida para: {$stage->name}");
    }

    public function win(Deal $deal): RedirectResponse
    {
        $deal->markWon();
        $this->log($deal, 'Oportunidade ganha 🎉', 'positive');

        return back()->with('success', 'Oportunidade marcada como GANHA! Valor: R$ ' . number_format((float) $deal->value, 2, ',', '.'));
    }

    public function lose(Request $request, Deal $deal): RedirectResponse
    {
        $validated = $request->validate(['lost_reason' => ['required', 'string', 'max:255']], [
            'lost_reason.required' => 'Informe o motivo da perda — isso alimenta seus relatórios.',
        ]);

        $deal->markLost($validated['lost_reason']);
        $this->log($deal, 'Oportunidade perdida: ' . $validated['lost_reason'], 'negative');

        return back()->with('success', 'Oportunidade marcada como perdida.');
    }

    public function reopen(Deal $deal): RedirectResponse
    {
        $deal->reopen();

        return back()->with('success', 'Oportunidade reaberta.');
    }

    public function destroy(Deal $deal): RedirectResponse
    {
        $deal->delete();

        return redirect()->route('deals.index')->with('success', 'Oportunidade excluída.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'string', OwnedRule::exists('clients')],
            'pipeline_stage_id' => ['nullable', 'string', OwnedRule::exists('pipeline_stages', false)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'value' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'expected_close_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', Rule::in(array_keys(Client::SOURCES))],
        ], [
            'client_id.required' => 'Selecione um cliente.',
            'client_id.exists' => 'Cliente inválido.',
            'title.required' => 'O título é obrigatório.',
            'value.required' => 'Informe o valor estimado.',
        ]);
    }

    private function log(Deal $deal, string $title, string $outcome): void
    {
        Activity::create([
            'client_id' => $deal->client_id,
            'deal_id' => $deal->id,
            'type' => 'note',
            'title' => "{$title} — {$deal->title}",
            'happened_at' => now(),
            'outcome' => $outcome,
        ]);
    }

    /** % de oportunidades ganhas entre as fechadas nos últimos 90 dias. */
    private function conversionRate(): ?float
    {
        $closed = Deal::whereIn('status', [Deal::STATUS_WON, Deal::STATUS_LOST])
            ->where('closed_at', '>=', now()->subDays(90))
            ->get(['status']);

        if ($closed->isEmpty()) {
            return null;
        }

        return round($closed->where('status', Deal::STATUS_WON)->count() / $closed->count() * 100, 1);
    }
}
