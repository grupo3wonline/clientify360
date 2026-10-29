<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $form->title }}</title>
    {{-- CSS próprio e enxuto (sem Tailwind CDN): a página pública precisa carregar rápido no 4G. --}}
    <style>
        :root { --c: {{ $form->color }}; --fg: {{ $form->buttonTextColor() }}; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: {{ config('brand.colors.surface.100') }}; color: {{ config('brand.colors.surface.900') }}; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .card { width: 100%; max-width: 460px; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px -10px rgba(15, 23, 42, .15); overflow: hidden; }
        .bar { height: 6px; background: var(--c); }
        .body { padding: 28px; }
        h1 { font-size: 22px; margin: 0 0 6px; line-height: 1.25; }
        p.lead { margin: 0 0 20px; color: {{ config('brand.colors.surface.600') }}; font-size: 14px; line-height: 1.5; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: {{ config('brand.colors.surface.700') }}; }
        .req { color: {{ config('brand.colors.danger.600') }}; }
        .field { margin-bottom: 14px; }
        input, textarea { width: 100%; font: inherit; font-size: 15px; padding: 11px 13px; border: 1px solid {{ config('brand.colors.surface.300') }}; border-radius: 12px; background: #fff; color: inherit; transition: border-color .15s, box-shadow .15s; }
        input:focus, textarea:focus { outline: none; border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 20%, transparent); }
        textarea { min-height: 96px; max-height: 50vh; resize: vertical; }
        .err { color: {{ config('brand.colors.danger.600') }}; font-size: 12px; margin-top: 4px; }
        .consent { display: flex; gap: 8px; align-items: flex-start; font-size: 12px; color: {{ config('brand.colors.surface.500') }}; font-weight: 400; margin: 6px 0 18px; }
        .consent input { width: auto; margin-top: 2px; }
        button { width: 100%; border: 0; border-radius: 12px; padding: 13px; font: inherit; font-weight: 700; font-size: 15px; color: var(--fg); background: var(--c); cursor: pointer; transition: filter .15s, opacity .15s; }
        button:hover { filter: brightness(1.08); }
        button[disabled] { opacity: .7; cursor: progress; filter: none; }
        .req-note { font-size: 12px; color: {{ config('brand.colors.surface.500') }}; margin: 0 0 16px; }
        :focus-visible { outline: 2px solid var(--c); outline-offset: 2px; border-radius: 4px; }
        button:focus-visible, a:focus-visible, .consent input:focus-visible { outline: 2px solid var(--c); outline-offset: 2px; }
        .hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .ok { text-align: center; padding: 20px 0; }
        .ok .icon { width: 64px; height: 64px; border-radius: 50%; background: color-mix(in srgb, var(--c) 15%, #fff); color: var(--c); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 30px; }
        footer { text-align: center; font-size: 11px; color: {{ config('brand.colors.surface.400') }}; padding: 0 0 16px; }
        footer a { color: inherit; }
        @media (prefers-color-scheme: dark) {
            body { background: {{ config('brand.colors.surface.950') }}; color: {{ config('brand.colors.surface.200') }}; }
            .card { background: {{ config('brand.colors.surface.900') }}; box-shadow: none; border: 1px solid {{ config('brand.colors.surface.800') }}; }
            p.lead, .consent, .req-note { color: {{ config('brand.colors.surface.400') }}; } label { color: {{ config('brand.colors.surface.300') }}; }
            input, textarea { background: {{ config('brand.colors.surface.800') }}; border-color: {{ config('brand.colors.surface.700') }}; }
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

                @if($errors->any())
                    <div class="err" role="alert" style="margin: 0 0 16px; padding: 10px 12px; border: 1px solid {{ config('brand.colors.danger.300') }}; border-radius: 10px; background: color-mix(in srgb, {{ config('brand.colors.danger.600') }} 8%, transparent);">
                        Não foi possível enviar. Verifique os campos destacados abaixo.
                    </div>
                @endif

                <form autocomplete="off" method="POST" action="{{ route('lead-forms.submit', $form->slug) }}" novalidate data-lead-form>
                    @csrf
                    @foreach(['utm_source', 'utm_medium', 'utm_campaign'] as $utm)
                        @if(request($utm))<input autocomplete="off" type="hidden" name="{{ $utm }}" value="{{ request($utm) }}">@endif
                    @endforeach

                    @foreach($form->fields as $field)
                        @php $id = 'f_' . $field['name']; $err = $errors->first('data.' . $field['name']); @endphp
                        <div class="field">
                            <label for="{{ $id }}">{{ $field['label'] }} @if(!empty($field['required']))<span class="req" aria-hidden="true">*</span>@endif</label>
                            @if($field['type'] === 'textarea')
                                <textarea autocomplete="off" id="{{ $id }}" name="data[{{ $field['name'] }}]" maxlength="3000" @if(!empty($field['required'])) required @endif
                                          @if($err) aria-invalid="true" aria-describedby="{{ $id }}_err" @endif>{{ old('data.' . $field['name']) }}</textarea>
                            @else
                                <input id="{{ $id }}" type="{{ $field['type'] }}" name="data[{{ $field['name'] }}]" value="{{ old('data.' . $field['name']) }}" maxlength="255"
                                       @if(!empty($field['required'])) required @endif
                                       @if($err) aria-invalid="true" aria-describedby="{{ $id }}_err" @endif
                                       autocomplete="off">
                            @endif
                            @if($err)<div class="err" id="{{ $id }}_err">{{ $err }}</div>@endif
                        </div>
                    @endforeach

                    @if(collect($form->fields)->contains(fn ($f) => !empty($f['required'])))
                        <p class="req-note"><span class="req" aria-hidden="true">*</span> Campos obrigatórios</p>
                    @endif

                    {{-- Honeypot anti-spam: invisível para pessoas --}}
                    <div class="hp" aria-hidden="true"><label for="website_url">Não preencha</label><input id="website_url" name="website_url" tabindex="-1" autocomplete="off"></div>

                    <label class="consent"><input autocomplete="off" type="checkbox" name="lgpd_consent" value="1" required @checked(old('lgpd_consent'))>
                        <span>Autorizo o contato e o tratamento dos meus dados para esta finalidade, conforme a LGPD.</span></label>
                    @error('lgpd_consent')<div class="err" style="margin:-10px 0 14px">{{ $message }}</div>@enderror

                    @error('g-recaptcha-response')<div class="err" style="margin:-10px 0 14px">{{ $message }}</div>@enderror

                    <x-recaptcha action="lead" />

                    <button type="submit" data-submit>{{ $form->button_text }}</button>
                </form>

                {{-- Loading state sem framework: o botão desabilita no envio para evitar
                     duplo clique enquanto o token do reCAPTCHA é gerado. A validação é
                     100% server-side (novalidate), então basta reagir ao submit. --}}
                <script>
                    (function () {
                        var form = document.querySelector('[data-lead-form]');
                        if (!form) { return; }
                        var btn = form.querySelector('[data-submit]');
                        if (!btn) { return; }
                        form.addEventListener('submit', function () {
                            btn.disabled = true;
                            btn.textContent = 'Enviando…';
                        });
                    })();
                </script>
            @endif
        </div>
        <footer>Formulário seguro · <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacidade</a><br>Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL</footer>
    </main>
</body>
</html>
