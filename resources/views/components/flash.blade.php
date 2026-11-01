{{-- Session flash messages shown with SweetAlert2 (single place for every layout). --}}
@php
    $flashes = collect([
        'success' => ['Tudo certo', 'success'],
        'info' => ['Informação', 'info'],
        'warning' => ['Atenção', 'warning'],
        'error' => ['Ops, algo deu errado', 'error'],
    ])->filter(fn (array $meta, string $key): bool => filled(session($key)))
        ->map(fn (array $meta, string $key): array => ['title' => $meta[0], 'icon' => $meta[1], 'text' => (string) session($key)])
        ->values();
@endphp
@if($flashes->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var queue = [
                @foreach($flashes as $flash)
                    { title: @js($flash['title']), text: @js($flash['text']), icon: @js($flash['icon']) },
                @endforeach
            ];
            var next = function () {
                var item = queue.shift();
                if (!item || !window.Swal) { return; }
                window.Swal.fire({
                    title: item.title,
                    text: item.text,
                    icon: item.icon,
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: item.icon === 'error' ? window.brandColors.danger['600'] : window.brandColors.brand['600'],
                }).then(next);
            };
            next();
        });
    </script>
@endif
