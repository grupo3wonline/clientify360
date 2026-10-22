<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Orçamento {{ $quote->number }} — {{ $quote->client?->name }}</title>
    {{-- Página autocontida (sem Tailwind CDN) para imprimir rápido e igual em qualquer navegador. "Salvar como PDF" gera o arquivo. --}}
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; color: #0f172a; margin: 0; background: #f1f5f9; font-size: 13px; line-height: 1.5; }
        .page { max-width: 820px; margin: 24px auto; background: #fff; padding: 48px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #4f46e5; padding-bottom: 20px; margin-bottom: 28px; gap: 24px; }
        h1 { margin: 0; font-size: 22px; color: #4f46e5; }
        .muted { color: #64748b; }
        .box { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; }
        .box div { background: #f8fafc; border-radius: 8px; padding: 12px 14px; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 8px 6px; }
        td { padding: 10px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .r { text-align: right; white-space: nowrap; }
        .totals { margin-left: auto; width: 280px; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 0; }
        .totals .grand { border-top: 2px solid #0f172a; margin-top: 6px; padding-top: 8px; font-size: 17px; font-weight: 700; }
        .notes { margin-top: 28px; white-space: pre-line; }
        .sign { margin-top: 64px; display: flex; gap: 48px; }
        .sign div { flex: 1; border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; font-size: 11px; color: #64748b; }
        .toolbar { max-width: 820px; margin: 16px auto 0; display: flex; justify-content: flex-end; gap: 8px; }
        .toolbar button { background: #4f46e5; color: #fff; border: 0; padding: 10px 18px; border-radius: 10px; font-weight: 600; cursor: pointer; }
        @media print { body { background: #fff; } .page { box-shadow: none; margin: 0; padding: 0; max-width: none; } .toolbar { display: none; } }
        @media (max-width: 640px) { .page { padding: 20px; margin: 0; border-radius: 0; } .box { grid-template-columns: 1fr; } header { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">Imprimir / Salvar PDF</button></div>
    <main class="page">
        <header>
            <div>
                <h1>{{ $user->name }}</h1>
                <p class="muted" style="margin:4px 0 0">{{ $user->email }}</p>
            </div>
            <div style="text-align:right">
                <div class="label">Orçamento</div>
                <div style="font-size:18px;font-weight:700">{{ $quote->number }}</div>
                <div class="muted">Emitido em {{ $quote->created_at->format('d/m/Y') }}</div>
                @if($quote->valid_until)<div class="muted">Válido até {{ $quote->valid_until->format('d/m/Y') }}</div>@endif
            </div>
        </header>

        <section class="box">
            <div>
                <div class="label">Cliente</div>
                <strong>{{ $quote->client?->name }}</strong><br>
                @if($quote->client?->document){{ $quote->client->document }}<br>@endif
                @if($quote->client?->email){{ $quote->client->email }}<br>@endif
                @if($quote->client?->phone){{ $quote->client->phone }}@endif
            </div>
            <div>
                <div class="label">Referente a</div>
                <strong>{{ $quote->title }}</strong>
                @if($quote->client?->city)<br><span class="muted">{{ $quote->client->city }}{{ $quote->client->state ? '/' . $quote->client->state : '' }}</span>@endif
            </div>
        </section>

        <table>
            <thead><tr><th style="width:50%">Descrição</th><th class="r">Qtd.</th><th class="r">Valor unit.</th><th class="r">Total</th></tr></thead>
            <tbody>
                @foreach($quote->items as $item)
                    <tr><td>{{ $item->description }}</td><td class="r">@qty($item->quantity)</td><td class="r">@money($item->unit_price)</td><td class="r">@money($item->total)</td></tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span class="muted">Subtotal</span><span>@money($quote->subtotal)</span></div>
            @if((float) $quote->discount > 0)<div><span class="muted">Desconto</span><span>− @money($quote->discount)</span></div>@endif
            <div class="grand"><span>Total</span><span>@money($quote->total)</span></div>
        </div>

        @if($quote->payment_terms)<div class="notes"><div class="label">Condições de pagamento</div>{{ $quote->payment_terms }}</div>@endif
        @if($quote->notes)<div class="notes"><div class="label">Observações</div>{{ $quote->notes }}</div>@endif

        <div class="sign"><div>{{ $user->name }}</div><div>{{ $quote->client?->name }} — De acordo</div></div>
    </main>
</body>
</html>
