@extends('layouts.sidebar')
@section('title', 'Dashboard')

@section('content')
<div class="page-head"><h1>Dashboard</h1></div>

<div class="cards">
    <div class="card"><div class="label">Payin wallet</div><div class="value">{{ number_format($stats['payin_wallet'], 2) }}</div></div>
    <div class="card"><div class="label">Payout wallet</div><div class="value">{{ number_format($stats['payout_wallet'], 2) }}</div></div>
    <div class="card"><div class="label">All wallet</div><div class="value">{{ number_format($stats['all_wallet'], 2) }}</div></div>
    <div class="card"><div class="label">Quota</div><div class="value">{{ number_format($stats['quota'], 2) }}</div></div>
</div>

<div class="cards">
    @if(! $me->isMerchant())
        <div class="card"><div class="label">Merchants</div><div class="value">{{ $stats['merchants'] }}</div></div>
    @endif
    @if($me->isStaffSide() || $me->isCustomer())
        <div class="card"><div class="label">Customers</div><div class="value">{{ $stats['customers'] }}</div></div>
    @endif
    <div class="card"><div class="label">Payin today</div><div class="value">{{ number_format($stats['payin_today'], 2) }}</div></div>
    <div class="card"><div class="label">Pending payins</div><div class="value">{{ $stats['pending_payins'] }}</div></div>
    <div class="card"><div class="label">Pending settlements</div><div class="value" style="color:var(--amber)">{{ $stats['pending_settlements'] }}</div></div>
</div>

@if($me->isMerchant())
<div class="panel">
    <h2>Your API credentials</h2>
    <p class="muted">Use these to create payins from your system (<code class="k">POST /api/pg/payin</code>).</p>
    <div class="field"><label>X-API-KEY</label><code class="k">{{ $me->api_key }}</code></div>
    <div class="field"><label>X-API-TOKEN</label><code class="k">{{ $me->api_token }}</code></div>
</div>
@endif
@endsection
