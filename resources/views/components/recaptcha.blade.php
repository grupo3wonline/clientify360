@props(['action' => 'submit'])

@php
    // reCAPTCHA v3 (classic). Renders nothing when the site key is empty, so dev
    // environments and tests (no keys) work without any token.
    $siteKey = config('services.recaptcha.site_key');
@endphp

@if(filled($siteKey))
    @once
        <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}"></script>
    @endonce

    @php $widgetId = 'recaptcha_'.\Illuminate\Support\Str::random(8); @endphp

    <input type="hidden" name="g-recaptcha-response" id="{{ $widgetId }}" autocomplete="off">

    <script>
        (function () {
            var input = document.getElementById(@js($widgetId));
            if (!input) {
                return;
            }

            var form = input.closest('form');
            if (!form) {
                return;
            }

            var siteKey = @js($siteKey);
            var action = @js($action);
            var submitted = false;

            form.addEventListener('submit', function (event) {
                if (submitted) {
                    return;
                }

                event.preventDefault();
                if (window.loading) { window.loading.show(form.dataset.loadingText); }

                if (typeof grecaptcha === 'undefined') {
                    // reCAPTCHA failed to load: submit anyway so the server-side
                    // rule decides (fail-closed when enabled).
                    submitted = true;
                    form.submit();
                    return;
                }

                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                        input.value = token;
                        submitted = true;
                        form.submit();
                    });
                });
            });
        })();
    </script>
@endif
