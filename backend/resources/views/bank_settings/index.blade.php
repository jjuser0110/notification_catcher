@extends('layouts.sidebar')
@section('title', 'Bank Accounts')

@section('content')
<div class="page-head"><h1>Bank Accounts</h1></div>

<form method="POST" action="{{ route('bank_settings.store') }}" class="panel" style="max-width:760px;">
    @csrf
    <h2>Add bank account</h2>
    <div class="row">
        <div class="field"><label>Bank</label>
            <select name="bank_id" required>
                @foreach($banks as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </select></div>
        <div class="field"><label>Account holder</label><input type="text" name="fullname" required></div>
    </div>
    <div class="row">
        <div class="field"><label>Account number</label><input type="text" name="account_no" required></div>
        <div class="field"><label>Amount / limit</label><input type="number" step="0.01" name="amount" value="0"></div>
    </div>
    <div class="row" style="align-items:center;">
        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="for_payin" value="1" checked style="width:auto;"> For payin</label>
        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="for_payout" value="1" style="width:auto;"> For payout</label>
        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active</label>
        <button class="btn" type="submit">Add account</button>
    </div>
</form>

@if($settings->isEmpty())
    <div class="panel muted" style="text-align:center;">No bank accounts yet.</div>
@else
<table>
    <thead><tr><th>Bank</th><th>Account</th><th>Holder</th><th class="right">Amount</th><th>Use</th><th>Status</th><th>By</th></tr></thead>
    <tbody>
        @foreach($settings as $s)
        <tr>
            <td>{{ $s->bank?->name ?? '—' }}</td>
            <td>{{ $s->account_no }}</td>
            <td>{{ $s->fullname }}</td>
            <td class="right">{{ number_format($s->amount,2) }}</td>
            <td class="muted">{{ $s->for_payin ? 'Payin' : '' }} {{ $s->for_payout ? 'Payout' : '' }}</td>
            <td><span class="tag {{ $s->is_active ? 'on' : 'off' }}">{{ $s->is_active ? 'Active' : 'Off' }}</span></td>
            <td class="muted">{{ $s->creator?->username ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div>{{ $settings->links() }}</div>
@endif
@endsection
