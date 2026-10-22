<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class TagController extends Controller
{
    public function __construct(
        private readonly TagService $service
    ) {}

    public function index(): View
    {
        $tags = Tag::withCount('clients')->orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', 'Tag criada!');
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->service->update($tag, $request->validated());

        return back()->with('success', 'Tag atualizada!');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->service->delete($tag);

        return back()->with('success', 'Tag excluída.');
    }
}
