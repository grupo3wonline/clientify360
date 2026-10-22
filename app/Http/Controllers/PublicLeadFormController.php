<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LeadForm;
use App\Services\LeadCaptureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Formulário público /f/{slug} — sem login, com rate limit e honeypot anti-spam. */
final class PublicLeadFormController extends Controller
{
    public function __construct(
        private readonly LeadCaptureService $capture,
    ) {}

    public function show(string $slug): View
    {
        return view('lead-forms.public', ['form' => $this->find($slug)]);
    }

    public function submit(Request $request, string $slug): RedirectResponse
    {
        $form = $this->find($slug);

        // Honeypot: campo invisível que só robô preenche. Fingimos sucesso para não dar pista.
        if (filled($request->input('website_url'))) {
            return back()->with('lead_success', $form->success_message ?: 'Obrigado!');
        }

        $rules = [];
        $messages = [];
        foreach ($form->fields as $field) {
            $key = "data.{$field['name']}";
            $rules[$key] = array_filter([
                !empty($field['required']) ? 'required' : 'nullable',
                'string',
                $field['type'] === 'email' ? 'email' : null,
                $field['type'] === 'textarea' ? 'max:3000' : 'max:255',
            ]);
            $messages["{$key}.required"] = "O campo \"{$field['label']}\" é obrigatório.";
            $messages["{$key}.email"] = 'Informe um e-mail válido.';
        }
        $rules['lgpd_consent'] = ['accepted'];
        $messages['lgpd_consent.accepted'] = 'É preciso autorizar o contato para enviar.';

        $validated = $request->validate($rules, $messages);

        $this->capture->handle($form, $validated['data'] ?? [], [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
        ]);

        // O redirecionamento externo acontece na página de confirmação (JS), e não via
        // HTTP 302: a CSP "form-action 'self'" bloquearia um 302 para outro domínio.
        return back()
            ->with('lead_success', $form->success_message ?: 'Obrigado! Entraremos em contato em breve.')
            ->with('lead_redirect', $form->redirect_url);
    }

    private function find(string $slug): LeadForm
    {
        // Sem usuário logado o escopo global não filtra; buscamos por slug + ativo
        return LeadForm::withoutGlobalScope('owned')->where('slug', $slug)->where('is_active', true)->firstOrFail();
    }
}
