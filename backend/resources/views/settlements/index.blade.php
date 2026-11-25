@extends('layouts.sidebar')
@section('title', 'Settlements')

@section('content')
<div class="page-head"><h1>Settlements</h1>
    <form method="GET" class="inline-form">
        <select name="status" onchange="this.form.submit()" style="width:auto;display:inline-block;">
            <option value="">All status</option>
            @foreach(['pending','approved','rejected'] as $s)
                <option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
    @if(auth()->user()->isCustomer())
        <a href="{{ route('settlements.create') }}" class="btn">+ New settlement</a>
    @endif
</div>

@if($settlements->isEmpty())
    <div class="panel muted" style="text-align:center;">No settlements yet.</div>
@else
<table>
    <thead>
        <tr><th>#</th><th>Agent</th><th>To bank</th><th class="right">Payin</th><th class="right">Payout</th>
            <th class="right">Total</th><th>Merchants</th><th>Status</th><th>Submitted</th><th class="right"></th></tr>
    </thead>
    <tbody>
        @foreach($settlements as $s)
        <tr>
            <td data-label="#">#{{ $s->id }}</td>
            <td data-label="Agent">{{ $s->user->username }}</td>
            <td data-label="To bank">{{ $s->settlementBank?->bank?->name ?? '—' }}<div class="muted">{{ $s->settlementBank?->account_no }}</div></td>
            <td data-label="Payin" class="right">{{ number_format($s->payin_amount,2) }}</td>
            <td data-label="Payout" class="right">{{ number_format($s->payout_amount,2) }}</td>
            <td data-label="Total" class="right"><strong>{{ number_format($s->total_amount,2) }}</strong></td>
            <td data-label="Merchants" class="muted">{{ $s->items->count() }}</td>
            <td data-label="Status"><span class="tag {{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
            <td data-label="Submitted" class="muted">{{ $s->created_at->diffForHumans() }}</td>
            <td class="right actions" style="white-space:nowrap;">
                @if($canApprove && $s->status === 'pending')
                <form method="POST" action="{{ route('settlements.approve', $s) }}" class="inline-form">@csrf
                    <button class="btn sm green">Approve</button></form>
                <form method="POST" action="{{ route('settlements.reject', $s) }}" class="inline-form">@csrf
                    <button class="btn sm danger">Reject</button></form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div>{{ $settlements->links() }}</div>
@endif
@endsection
