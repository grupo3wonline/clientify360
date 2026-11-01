@php($account = $quote->account)
@extends('pdf.layout')

@section('title', 'Orçamento nº '.$quote->number)

@section('document')
    <div class="label">Documento</div>
    <div style="font-size: 14px; font-weight: bold;">Orçamento nº {{ $quote->number }}</div>
    <div class="muted">Emitido em {{ $quote->created_at?->format('d/m/Y') }}</div>
    @if($quote->valid_until)
        <div class="muted">Válido até {{ $quote->valid_until->format('d/m/Y') }}</div>
    @endif
    <div class="muted">Situação: {{ $quote->statusLabel() }}</div>
@endsection

@section('content')
    <table class="box">
        <tr>
            <td>
                <div class="label">Cliente</div>
                <strong>{{ $quote->client?->name }}</strong><br>
                @if($quote->client?->document){{ $quote->client->formattedDocument() }}<br>@endif
                @if($quote->client?->email){{ $quote->client->email }}<br>@endif
                @if($quote->client?->phone){{ $quote->client->phone }}@endif
            </td>
            <td>
                <div class="label">Referente a</div>
                <strong>{{ $quote->title }}</strong>
                @if($quote->client?->city)
                    <br><span class="muted">{{ $quote->client->city }}{{ $quote->client->state ? '/'.$quote->client->state : '' }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 52%;">Descrição</th>
                <th class="r">Qtd.</th>
                <th class="r">Valor unit.</th>
                <th class="r">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="r">@qty($item->quantity)</td>
                    <td class="r">@money($item->unit_price)</td>
                    <td class="r">@money($item->total)</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="muted">Subtotal</td><td class="r">@money($quote->subtotal)</td></tr>
        @if((float) $quote->discount > 0)
            <tr><td class="muted">Desconto</td><td class="r">− @money($quote->discount)</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="r">@money($quote->total)</td></tr>
    </table>

    @if($quote->payment_terms)
        <div class="notes"><div class="label">Condições de pagamento</div><div class="pre">{{ $quote->payment_terms }}</div></div>
    @endif
    @if($quote->notes)
        <div class="notes"><div class="label">Observações</div><div class="pre">{{ $quote->notes }}</div></div>
    @endif

    <table class="sign">
        <tr>
            <td><div>{{ $account?->name }}</div></td>
            <td><div>{{ $quote->client?->name }} — De acordo</div></td>
        </tr>
    </table>
@endsection
