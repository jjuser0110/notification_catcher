@extends('layouts.sidebar')
@section('title', $payin->transaction_no)

@section('content')
<div class="page-head"><h1>Payin {{ $payin->transaction_no }}</h1>
    <a href="{{ route('payins.index') }}" class="btn ghost">Back</a>
    @if($payin->status === 'pending' && auth()->user()->isStaffSide())
    <form method="POST" action="{{ route('payins.approve', $payin) }}" class="inline-form">@csrf
        <button class="btn green">Approve &amp; split</button></form>
    @endif
</div>

<div class="panel" style="max-width:720px;">
    <table>
        <tbody>
            <tr><th>Status</th><td><span class="tag {{ $payin->status }}">{{ ucfirst($payin->status) }}</span></td></tr>
            <tr><th>Merchant</th><td>{{ $payin->merchant->username }} ({{ $payin->merchant->name }})</td></tr>
            <tr><th>Amount</th><td>{{ number_format($payin->amount,2) }}</td></tr>
            <tr><th>Rate</th><td>{{ $payin->rate !== null ? number_format($payin->rate,2).'%' : '—' }}</td></tr>
            <tr><th>Nett amount</th><td>{{ $payin->nett_amount !== null ? number_format($payin->nett_amount,2) : '—' }}</td></tr>
            <tr><th>Reference</th><td>{{ $payin->reference_no ?? '—' }}</td></tr>
            <tr><th>Player</th><td>{{ $payin->player ?? '—' }}</td></tr>
            <tr><th>Bank account</th><td>{{ $payin->bankSetting?->bank?->name ?? '—' }} {{ $payin->bankSetting?->account_no }}</td></tr>
            <tr><th>IP</th><td class="muted">{{ $payin->ip_address ?? '—' }}</td></tr>
            <tr><th>Approved</th><td>{{ $payin->approved_date?->format('Y-m-d H:i') ?? '—' }}
                @if($payin->manualUpdateBy) by {{ $payin->manualUpdateBy->username }}@endif</td></tr>
            <tr><th>Created</th><td>{{ $payin->created_at->format('Y-m-d H:i') }}</td></tr>
        </tbody>
    </table>
</div>

@if($payin->logs->isNotEmpty())
<div class="panel">
    <h2>Wallet split</h2>
    <table>
        <thead><tr><th>User</th><th class="right">Rate %</th><th class="right">Reload</th><th class="right">Earned</th><th>When</th></tr></thead>
        <tbody>
            @foreach($payin->logs as $log)
            <tr>
                <td>{{ $log->user->username }} <span class="muted">({{ $log->user->role }})</span></td>
                <td class="right">{{ number_format($log->rate,2) }}</td>
                <td class="right">{{ number_format($log->reload_amount,2) }}</td>
                <td class="right">{{ number_format($log->amount,2) }}</td>
                <td class="muted">{{ $log->date_of_record->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
