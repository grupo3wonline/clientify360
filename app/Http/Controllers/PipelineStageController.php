<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PipelineStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PipelineStageController extends Controller
{
    public function index(): View
    {
        PipelineStage::ensureDefaultsFor((string) auth()->user()->account_id);
        $stages = PipelineStage::withCount(['deals' => fn ($q) => $q->where('status', 'open')])->orderBy('position')->get();

        return view('deals.stages', compact('stages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['position'] = (int) PipelineStage::max('position') + 1;
        PipelineStage::create($data + ['is_active' => true]);

        return back()->with('success', 'Etapa criada!');
    }

    public function update(Request $request, PipelineStage $stage): RedirectResponse
    {
        $data = $this->validated($request) + $request->validate(['position' => ['required', 'integer', 'min:1', 'max:99']]);
        $stage->update($data);

        return back()->with('success', 'Etapa atualizada!');
    }

    public function destroy(PipelineStage $stage): RedirectResponse
    {
        if ($stage->deals()->where('status', 'open')->exists()) {
            return back()->with('error', 'Mova as oportunidades abertas desta etapa antes de excluí-la.');
        }

        if (PipelineStage::count() <= 1) {
            return back()->with('error', 'O funil precisa ter pelo menos uma etapa.');
        }

        $stage->delete();

        return back()->with('success', 'Etapa excluída.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'probability' => ['required', 'integer', 'between:0,100'],
        ], [
            'name.required' => 'O nome da etapa é obrigatório.',
            'color.regex' => 'Cor inválida.',
        ]);
    }
}
