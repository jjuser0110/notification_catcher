@extends('layouts.sidebar')
@section('title', 'Dashboard')

@section('content')
<div class="hero">
    <div class="hero-eye">/// PAYMENTS IN MOTION</div>
    <div class="hero-flex">
        <div>
            <div class="hero-label">Payins approved today</div>
            <div class="hero-value"><span class="cur">MYR</span>{{ number_format($stats['payin_today'], 2) }}</div>
            <div class="hero-sub">{{ $stats['pending_payins'] }} pending · available balance MYR {{ number_format($stats['all_wallet'], 2) }}</div>
        </div>
        <div class="hero-chip">
            <div class="k">Pending settlements</div>
            <div class="v">{{ $stats['pending_settlements'] }}</div>
        </div>
    </div>
</div>

<div class="cards">
    <div class="card">
        <div class="ico">{!! hpayIcon('payin') !!}</div>
        <div class="label">Payin wallet</div>
        <div class="value">{{ number_format($stats['payin_wallet'], 2) }}</div>
        <div class="sub">MYR available to pay in</div>
    </div>
    <div class="card">
        <div class="ico">{!! hpayIcon('settle') !!}</div>
        <div class="label">Payout wallet</div>
        <div class="value">{{ number_format($stats['payout_wallet'], 2) }}</div>
        <div class="sub">MYR available to pay out</div>
    </div>
    <div class="card">
        <div class="ico">{!! hpayIcon('bank') !!}</div>
        <div class="label">All wallet</div>
        <div class="value">{{ number_format($stats['all_wallet'], 2) }}</div>
        <div class="sub">MYR total balance</div>
    </div>
    <div class="card">
        <div class="ico">{!! hpayIcon('sliders') !!}</div>
        <div class="label">Quota</div>
        <div class="value">{{ number_format($stats['quota'], 2) }}</div>
        <div class="sub">MYR assigned quota</div>
    </div>
</div>

<div class="cards">
    @if(! $me->isMerchant())
        <div class="card">
            <div class="ico">{!! hpayIcon('store') !!}</div>
            <div class="label">Merchants</div>
            <div class="value">{{ $stats['merchants'] }}</div>
            <div class="sub">under your account</div>
        </div>
    @endif
    @if($me->isStaffSide() || $me->isCustomer())
        <div class="card">
            <div class="ico">{!! hpayIcon('users') !!}</div>
            <div class="label">Customers</div>
            <div class="value">{{ $stats['customers'] }}</div>
            <div class="sub">agents in your tree</div>
        </div>
    @endif
    <div class="card">
        <div class="ico">{!! hpayIcon('payin') !!}</div>
        <div class="label">Payin today</div>
        <div class="value">{{ number_format($stats['payin_today'], 2) }}</div>
        <div class="sub">MYR approved today</div>
    </div>
    <div class="card">
        <div class="ico">{!! hpayIcon('message') !!}</div>
        <div class="label">Pending payins</div>
        <div class="value">{{ $stats['pending_payins'] }}</div>
        <div class="sub">awaiting approval</div>
    </div>
    <div class="card">
        <div class="ico">{!! hpayIcon('settle') !!}</div>
        <div class="label">Pending settlements</div>
        <div class="value" style="color:var(--accent)">{{ $stats['pending_settlements'] }}</div>
        <div class="sub">batches to process</div>
    </div>
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
