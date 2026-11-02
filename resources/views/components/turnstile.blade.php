@props(['action' => 'submit'])

@php
    // Cloudflare Turnstile. Renders nothing when the site key is empty, so dev
    // environments and tests (no keys) work without any token.
    $siteKey = config('services.turnstile.site_key');
    $widgetId = 'turnstile_'.\Illuminate\Support\Str::random(8);
@endphp

@if(filled($siteKey))
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    @endonce

    {{-- Inline spacing and the "err" class: this component is also used on the public lead form,
         a standalone page without Tailwind (its own .err style defines the message color). --}}
    <div style="margin-top:16px" data-turnstile-wrapper>
        <div id="{{ $widgetId }}" style="min-height:65px;max-width:100%"></div>
        <p class="err text-xs text-danger-600 dark:text-danger-400" style="margin-top:6px" role="alert" hidden data-turnstile-message></p>
    </div>

    <script>
        (function () {
            var holder = document.getElementById(@js($widgetId));
            if (!holder) { return; }

            var form = holder.closest('form');
            var message = holder.parentNode.querySelector('[data-turnstile-message]');
            var token = '';
            var widget = null;
            var attempts = 0;

            function say(text) {
                if (!message) { return; }
                message.textContent = text || '';
                message.hidden = !text;
            }

            function mount() {
                if (!window.turnstile) {
                    // api.js is async: wait for it, but give up after ~10 s and tell the user.
                    if (++attempts > 100) {
                        say('Não foi possível carregar a verificação de segurança. Recarregue a página.');
                        return;
                    }
                    setTimeout(mount, 100);
                    return;
                }

                widget = window.turnstile.render(holder, {
                    sitekey: @js($siteKey),
                    action: @js($action),
                    theme: 'auto',
                    language: 'pt-br',
                    // The normal widget is 300 px wide; use the compact one on narrow screens.
                    size: holder.parentNode.clientWidth < 300 ? 'compact' : 'normal',
                    'refresh-expired': 'auto',
                    callback: function (value) { token = value; say(''); },
                    'expired-callback': function () { token = ''; },
                    'error-callback': function () {
                        token = '';
                        say('Falha na verificação de segurança. Recarregue a página e tente novamente.');
                    },
                });
            }

            if (form) {
                // Registered before the reCAPTCHA handler (x-captcha renders this component first), so a
                // submit without a token is stopped before reCAPTCHA or the loading overlay react.
                form.addEventListener('submit', function (event) {
                    if (token !== '') { return; }
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    say('Aguarde a verificação de segurança terminar e tente novamente.');
                });
            }

            // Back button (bfcache): the restored page holds a token that was already spent.
            window.addEventListener('pageshow', function (event) {
                if (event.persisted && widget !== null && window.turnstile) {
                    token = '';
                    window.turnstile.reset(widget);
                }
            });

            mount();
        })();
    </script>
@endif
