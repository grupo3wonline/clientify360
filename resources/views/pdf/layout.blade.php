<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    {{-- dompdf: CSS 2.1 only (no flex/grid), tables for layout, DejaVu Sans for accents. --}}
    <style>
        @page { margin: 32px 36px 60px 36px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: {{ config('brand.colors.surface.900') }}; line-height: 1.45; }
        h1 { font-size: 18px; margin: 0; }
        .muted { color: {{ config('brand.colors.surface.500') }}; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: {{ config('brand.colors.surface.500') }}; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .box { margin-top: 18px; border: 1px solid {{ config('brand.colors.surface.200') }}; }
        .box td { padding: 10px; vertical-align: top; width: 50%; }
        .items { margin-top: 18px; }
        .items th { background: {{ config('brand.colors.surface.100') }}; text-align: left; font-size: 9px; text-transform: uppercase; color: {{ config('brand.colors.surface.600') }}; padding: 6px; }
        .items td { padding: 7px 6px; border-bottom: 1px solid {{ config('brand.colors.surface.200') }}; vertical-align: top; }
        .r { text-align: right; white-space: nowrap; }
        .totals { margin-top: 12px; width: 45%; margin-left: 55%; }
        .totals td { padding: 3px 0; }
        .grand td { border-top: 2px solid {{ config('brand.colors.surface.900') }}; padding-top: 6px; font-size: 14px; font-weight: bold; }
        .notes { margin-top: 18px; }
        .pre { white-space: pre-line; }
        .sign { margin-top: 56px; }
        .sign td { width: 50%; padding: 0 16px; text-align: center; }
        .sign div { border-top: 1px solid {{ config('brand.colors.surface.400') }}; padding-top: 4px; font-size: 9px; color: {{ config('brand.colors.surface.500') }}; }
        .logo { max-height: 56px; max-width: 180px; }
        .footer { position: fixed; bottom: -40px; left: 0; right: 0; text-align: center; font-size: 8px; color: {{ config('brand.colors.surface.400') }}; border-top: 1px solid {{ config('brand.colors.surface.200') }}; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="footer">Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL · Gerado em {{ now()->format('d/m/Y H:i') }}</div>

    <table class="header">
        <tr>
            <td>
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $account?->name }}" class="logo"><br>
                @endif
                <h1>{{ $account?->name }}</h1>
            </td>
            <td class="r">
                @yield('document')
            </td>
        </tr>
    </table>

    @yield('content')
</body>
</html>
