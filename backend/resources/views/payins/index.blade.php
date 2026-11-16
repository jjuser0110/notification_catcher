@extends('layouts.sidebar')
@section('title', 'Payins')

@section('content')
<div class="page-head"><h1>Payins</h1>
    <form method="GET" class="inline-form">
        <select name="status" onchange="this.form.submit()" style="width:auto;display:inline-block;">
            <option value="">All status</option>
            @foreach(['pending','success','failed','cancelled'] as $s)
                <option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Txn / ref / player" style="width:200px;display:inline-block;">
    </form>
</div>

@if($payins->isEmpty())
    <div class="panel muted" style="text-align:center;">No payins found.</div>
@else
<table>
    <thead>
        <tr><th>Txn No</th><th>Merchant</th><th class="right">Amount</th><th class="right">Rate</th>
            <th class="right">Nett</th><th>Status</th><th>Created</th><th class="right"></th></tr>
    </thead>
    <tbody>
        @foreach($payins as $p)
        <tr>
            <td><a href="{{ route('payins.show', $p) }}">{{ $p->transaction_no }}</a>
                @if($p->reference_no)<div class="muted">{{ $p->reference_no }}</div>@endif</td>
            <td>{{ $p->merchant->username }}</td>
            <td class="right">{{ number_format($p->amount,2) }}</td>
            <td class="right">{{ $p->rate !== null ? number_format($p->rate,2) : '—' }}</td>
            <td class="right">{{ $p->nett_amount !== null ? number_format($p->nett_amount,2) : '—' }}</td>
            <td><span class="tag {{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
            <td class="muted">{{ $p->created_at->diffForHumans() }}</td>
            <td class="right">
                @if($p->status === 'pending' && auth()->user()->isStaffSide())
                <form method="POST" action="{{ route('payins.approve', $p) }}" class="inline-form">@csrf
                    <button class="btn sm green">Approve</button></form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div>{{ $payins->links() }}</div>
@endif
@endsection
