<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $form->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- CSS próprio e enxuto (sem Tailwind CDN): a página pública precisa carregar rápido no 4G. --}}
    <style>
        :root { --c: {{ $form->color }}; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; color: #0f172a; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .card { width: 100%; max-width: 460px; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px -10px rgba(15, 23, 42, .15); overflow: hidden; }
        .bar { height: 6px; background: var(--c); }
        .body { padding: 28px; }
        h1 { font-size: 22px; margin: 0 0 6px; line-height: 1.25; }
        p.lead { margin: 0 0 20px; color: #475569; font-size: 14px; line-height: 1.5; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #334155; }
        .req { color: #dc2626; }
        .field { margin-bottom: 14px; }
        input, textarea { width: 100%; font: inherit; font-size: 15px; padding: 11px 13px; border: 1px solid #cbd5e1; border-radius: 12px; background: #fff; color: inherit; transition: border-color .15s, box-shadow .15s; }
        input:focus, textarea:focus { outline: none; border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 20%, transparent); }
        textarea { min-height: 96px; resize: vertical; }
        .err { color: #dc2626; font-size: 12px; margin-top: 4px; }
        .consent { display: flex; gap: 8px; align-items: flex-start; font-size: 12px; color: #64748b; font-weight: 400; margin: 6px 0 18px; }
        .consent input { width: auto; margin-top: 2px; }
        button { width: 100%; border: 0; border-radius: 12px; padding: 13px; font: inherit; font-weight: 700; font-size: 15px; color: #fff; background: var(--c); cursor: pointer; transition: filter .15s; }
        button:hover { filter: brightness(1.08); }
        .hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .ok { text-align: center; padding: 20px 0; }
        .ok .icon { width: 64px; height: 64px; border-radius: 50%; background: color-mix(in srgb, var(--c) 15%, #fff); color: var(--c); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 30px; }
        footer { text-align: center; font-size: 11px; color: #94a3b8; padding: 0 0 16px; }
        footer a { color: inherit; }
        @media (prefers-color-scheme: dark) {
            body { background: #020617; color: #e2e8f0; }
            .card { background: #0f172a; box-shadow: none; border: 1px solid #1e293b; }
            p.lead, .consent { color: #94a3b8; } label { color: #cbd5e1; }
            input, textarea { background: #1e293b; border-color: #334155; }
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="bar"></div>
        <div class="body">
            @if(session('lead_success'))
                <div class="ok" role="status">
                    <div class="icon" aria-hidden="true">✓</div>
                    <h1>{{ session('lead_success') }}</h1>
                    @if(session('lead_redirect'))
                        <p class="lead">Redirecionando… <a href="{{ session('lead_redirect') }}">clique aqui</a> se não acontecer.</p>
                        <script>setTimeout(() => { window.location.href = @js(session('lead_redirect')); }, 1800);</script>
                    @endif
                </div>
            @else
                <h1>{{ $form->title }}</h1>
                @if($form->description)<p class="lead">{{ $form->description }}</p>@endif

                <form method="POST" action="{{ route('lead-forms.submit', $form->slug) }}" novalidate>
                    @csrf
                    @foreach(['utm_source', 'utm_medium', 'utm_campaign'] as $utm)
                        @if(request($utm))<input type="hidden" name="{{ $utm }}" value="{{ request($utm) }}">@endif
                    @endforeach

                    @foreach($form->fields as $field)
                        @php $id = 'f_' . $field['name']; $err = $errors->first('data.' . $field['name']); @endphp
                        <div class="field">
                            <label for="{{ $id }}">{{ $field['label'] }} @if(!empty($field['required']))<span class="req">*</span>@endif</label>
                            @if($field['type'] === 'textarea')
                                <textarea id="{{ $id }}" name="data[{{ $field['name'] }}]" maxlength="3000" autocomplete="off" @if(!empty($field['required'])) required @endif>{{ old('data.' . $field['name']) }}</textarea>
                            @else
                                <input id="{{ $id }}" type="{{ $field['type'] }}" name="data[{{ $field['name'] }}]" value="{{ old('data.' . $field['name']) }}" maxlength="255"
                                       @if(!empty($field['required'])) required @endif
                                       autocomplete="{{ ['name' => 'name', 'email' => 'email', 'phone' => 'tel', 'city' => 'address-level2', 'company' => 'organization'][$field['name']] ?? 'off' }}">
                            @endif
                            @if($err)<div class="err">{{ $err }}</div>@endif
                        </div>
                    @endforeach

                    {{-- Honeypot anti-spam: invisível para pessoas --}}
                    <div class="hp" aria-hidden="true"><label for="website_url">Não preencha</label><input id="website_url" name="website_url" tabindex="-1" autocomplete="off"></div>

                    <label class="consent"><input type="checkbox" name="lgpd_consent" value="1" required autocomplete="off" @checked(old('lgpd_consent'))>
                        <span>Autorizo o contato e o tratamento dos meus dados para esta finalidade, conforme a LGPD.</span></label>
                    @error('lgpd_consent')<div class="err" style="margin:-10px 0 14px">{{ $message }}</div>@enderror

                    <button type="submit">{{ $form->button_text }}</button>
                </form>
            @endif
        </div>
        <footer>Formulário seguro · <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacidade</a></footer>
    </main>
</body>
</html>
