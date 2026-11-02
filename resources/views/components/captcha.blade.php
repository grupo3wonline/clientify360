@props(['action' => 'submit'])

{{--
    Anti-bot checks of a public form: Cloudflare Turnstile (visible widget) + Google reCAPTCHA v3
    (invisible, runs on submit). Each one renders nothing when its site key is empty.
    The Turnstile handler must come first: it blocks a submit that has no token yet.
--}}
<x-turnstile :action="$action" />
<x-recaptcha :action="$action" />

@if($errors->has('g-recaptcha-response') || $errors->has('cf-turnstile-response'))
    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400" role="alert">
        {{ $errors->first('cf-turnstile-response') ?: $errors->first('g-recaptcha-response') }}
    </p>
@endif
