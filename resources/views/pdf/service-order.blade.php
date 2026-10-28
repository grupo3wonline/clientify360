@php($account = $order->account)
@extends('pdf.layout')

@section('title', 'Ordem de Serviço nº '.$order->number)

@section('document')
    <div class="label">Documento</div>
    <div style="font-size: 14px; font-weight: bold;">Ordem de Serviço nº {{ $order->number }}</div>
    <div class="muted">Aberta em {{ $order->opened_at?->format('d/m/Y') }}</div>
    @if($order->expected_at)
        <div class="muted">Previsão: {{ $order->expected_at->format('d/m/Y') }}</div>
    @endif
    @if($order->completed_at)
        <div class="muted">Concluída em {{ $order->completed_at->format('d/m/Y') }}</div>
    @endif
    <div class="muted">Situação: {{ $order->statusLabel() }}</div>
@endsection

@section('content')
    <table class="box">
        <tr>
            <td>
                <div class="label">Cliente</div>
                <strong>{{ $order->client?->name }}</strong><br>
                @if($order->client?->document){{ $order->client->formattedDocument() }}<br>@endif
                @if($order->client?->email){{ $order->client->email }}<br>@endif
                @if($order->client?->phone){{ $order->client->phone }}@endif
            </td>
            <td>
                <div class="label">Serviço</div>
                <strong>{{ $order->title }}</strong>
                @if($order->quote)
                    <br><span class="muted">Orçamento de origem: {{ $order->quote->number }}</span>
                @endif
            </td>
        </tr>
    </table>

    @if($order->description)
        <div class="notes"><div class="label">Descrição do serviço</div><div class="pre">{{ $order->description }}</div></div>
    @endif

    <table class="totals">
        <tr class="grand"><td>Valor total</td><td class="r">@money($order->total)</td></tr>
    </table>

    <table class="sign">
        <tr>
            <td><div>{{ $account?->name }}</div></td>
            <td><div>{{ $order->client?->name }} — Recebido / De acordo</div></td>
        </tr>
    </table>
@endsection
