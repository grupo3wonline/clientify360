{{--
    Persistent banner for the reminders that should not wait for the user to open the bell:
    password about to expire and subscription about to renew (7 days ahead). Uses the same
    service as the notification list, so both always agree.
--}}
@php
    $user = auth()->user();
    $alerts = $user ? app(\App\Services\AccountAlertService::class)->for($user) : [];
@endphp

@if($alerts !== [])
    <div class="space-y-3 mb-6" data-account-alerts>
        @foreach($alerts as $alert)
            @php($danger = $alert['level'] === 'danger')
            <div role="{{ $danger ? 'alert' : 'status' }}"
                 class="flex items-start gap-3 rounded-2xl border p-4 {{ $danger ? 'border-danger-200 bg-danger-50 text-danger-800 dark:border-danger-800 dark:bg-danger-950/40 dark:text-danger-200' : 'border-warning-200 bg-warning-50 text-warning-800 dark:border-warning-800 dark:bg-warning-950/40 dark:text-warning-200' }}">
                <i class="fa-solid {{ $alert['type'] === 'password_expiring' ? 'fa-key' : 'fa-credit-card' }} mt-0.5" aria-hidden="true"></i>
                <div class="min-w-0 flex-1 text-sm">
                    <p class="font-semibold">{{ $alert['title'] }}</p>
                    <p class="mt-0.5">{{ $alert['message'] }}</p>
                </div>
                <a href="{{ $alert['url'] }}" class="shrink-0 rounded-xl px-3 py-1.5 text-xs font-semibold underline underline-offset-2 hover:no-underline">
                    {{ $alert['type'] === 'password_expiring' ? 'Trocar senha' : 'Ver assinatura' }}
                </a>
            </div>
        @endforeach
    </div>
@endif
