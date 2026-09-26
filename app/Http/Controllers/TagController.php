<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount('clients')->orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        Tag::create($this->validated($request));

        return back()->with('success', 'Tag criada!');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $tag->update($this->validated($request, $tag));

        return back()->with('success', 'Tag atualizada!');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->clients()->detach();
        $tag->delete();

        return back()->with('success', 'Tag excluída.');
    }

    private function validated(Request $request, ?Tag $tag = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('tags')->where('user_id', auth()->id())->ignore($tag?->id)],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'name.required' => 'O nome da tag é obrigatório.',
            'name.unique' => 'Você já tem uma tag com esse nome.',
        ]);
    }
}
