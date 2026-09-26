<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\LeadForm;
use App\Models\LeadFormSubmission;
use App\Models\PipelineStage;
use App\Support\OwnedRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Gestão dos formulários de captação (área logada). */
final class LeadFormController extends Controller
{
    public function index(): View
    {
        $forms = LeadForm::latest()->get();
        $recent = LeadFormSubmission::with(['form:id,name', 'client:id,name'])->latest()->limit(10)->get();

        return view('lead-forms.index', compact('forms', 'recent'));
    }

    public function create(): View
    {
        $form = new LeadForm([
            'title' => 'Fale com a gente',
            'button_text' => 'Quero ser contatado',
            'color' => '#4f46e5',
            'default_source' => 'site',
            'create_deal' => true,
            'is_active' => true,
            'success_message' => 'Obrigado! Recebemos seus dados e entraremos em contato em breve.',
            'fields' => [
                ['name' => 'name', 'label' => 'Seu nome', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'required' => true],
                ['name' => 'phone', 'label' => 'WhatsApp', 'type' => 'tel', 'required' => false],
                ['name' => 'message', 'label' => 'Como podemos ajudar?', 'type' => 'textarea', 'required' => false],
            ],
        ]);

        return view('lead-forms.form', $this->formData($form));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = LeadForm::generateSlug($data['name']);
        $form = LeadForm::create($data);

        return redirect()->route('lead-forms.show', $form)->with('success', 'Formulário criado! Copie o link e divulgue.');
    }

    public function show(LeadForm $leadForm): View
    {
        $submissions = $leadForm->submissions()->with(['client:id,name', 'deal:id,title'])->paginate(20);

        return view('lead-forms.show', ['form' => $leadForm, 'submissions' => $submissions]);
    }

    public function edit(LeadForm $leadForm): View
    {
        return view('lead-forms.form', $this->formData($leadForm));
    }

    public function update(Request $request, LeadForm $leadForm): RedirectResponse
    {
        $leadForm->update($this->validated($request));

        return redirect()->route('lead-forms.show', $leadForm)->with('success', 'Formulário atualizado!');
    }

    public function toggle(LeadForm $leadForm): RedirectResponse
    {
        $leadForm->update(['is_active' => !$leadForm->is_active]);

        return back()->with('success', $leadForm->is_active ? 'Formulário ativado.' : 'Formulário pausado — o link deixa de aceitar envios.');
    }

    public function destroy(LeadForm $leadForm): RedirectResponse
    {
        $leadForm->delete();

        return redirect()->route('lead-forms.index')->with('success', 'Formulário excluído.');
    }

    private function formData(LeadForm $form): array
    {
        PipelineStage::ensureDefaultsFor((string) auth()->id());

        return [
            'form' => $form,
            'stages' => PipelineStage::ordered()->get(),
            'sources' => Client::SOURCES,
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'button_text' => ['required', 'string', 'max:50'],
            'success_message' => ['nullable', 'string', 'max:500'],
            'redirect_url' => ['nullable', 'url:http,https', 'max:500'],
            'default_source' => ['required', Rule::in(array_keys(Client::SOURCES))],
            'pipeline_stage_id' => ['nullable', 'string', OwnedRule::exists('pipeline_stages', false)],
            'fields' => ['required', 'array', 'min:1', 'max:15'],
            'fields.*.name' => ['required', 'string', 'max:40', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'],
            'fields.*.label' => ['required', 'string', 'max:80'],
            'fields.*.type' => ['required', Rule::in(array_keys(LeadForm::FIELD_TYPES))],
        ], [
            'name.required' => 'Dê um nome interno ao formulário.',
            'title.required' => 'O título público é obrigatório.',
            'fields.required' => 'Adicione pelo menos um campo.',
            'fields.*.name.regex' => 'O identificador do campo deve ter só letras minúsculas, números e _ (ex.: empresa, cargo).',
            'fields.*.name.distinct' => 'Há campos repetidos.',
            'redirect_url.url' => 'Informe uma URL válida começando com http:// ou https://',
        ]);

        $data['fields'] = collect($request->input('fields'))->values()->map(fn ($f) => [
            'name' => $f['name'],
            'label' => $f['label'],
            'type' => $f['type'],
            'required' => !empty($f['required']),
        ])->all();

        $data['create_deal'] = $request->boolean('create_deal');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
