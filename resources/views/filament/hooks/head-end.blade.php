{{-- Brand tokens as CSS variables (single source: resources/brand/tokens.json) + scoped styles for the shared components. --}}
@php
    $vars = collect(['brand', 'accent', 'surface'])
        ->flatMap(fn (string $palette): array => collect((array) config('brand.colors.'.$palette))
            ->mapWithKeys(fn (string $hex, string|int $shade): array => ["--c360-{$palette}-{$shade}" => $hex])
            ->all())
        ->map(fn (string $hex, string $name): string => $name.':'.e($hex))
        ->implode(';');
@endphp
<style>:root{ {{ $vars }} }</style>
@vite('resources/css/panel.css')
