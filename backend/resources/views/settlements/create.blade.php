@extends('layouts.sidebar')
@section('title', 'New settlement')

@section('content')
<div class="page-head"><h1>New settlement</h1>
    <a href="{{ route('settlements.index') }}" class="btn ghost">Back</a></div>

@if($settlementBanks->isEmpty())
    <div class="panel err flash">You need to add a settlement bank first.
        <a href="{{ route('settlement_banks.index') }}">Add one →</a></div>
@elseif($merchants->isEmpty())
    <div class="panel muted" style="text-align:center;">You have no merchants to settle.</div>
@else
<form method="POST" action="{{ route('settlements.store') }}">
    @csrf
    <div class="panel" style="max-width:420px;">
        <div class="field"><label>Settle into bank</label>
            <select name="settlement_bank_id" required>
                @foreach($settlementBanks as $sb)
                    <option value="{{ $sb->id }}">{{ $sb->bank->name }} — {{ $sb->account_no }} ({{ $sb->fullname }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="panel">
        <h2>Choose amounts per merchant</h2>
        <table>
            <thead><tr><th>Merchant</th><th class="right">Payin wallet</th><th class="right">Payin amount</th>
                <th class="right">Payout wallet</th><th class="right">Payout amount</th></tr></thead>
            <tbody>
                @foreach($merchants as $m)
                <tr>
                    <td><strong>{{ $m->username }}</strong><div class="muted">{{ $m->name }}</div></td>
                    <td class="right muted">{{ number_format($m->payin_wallet,2) }}</td>
                    <td class="right"><input type="number" step="0.01" min="0" max="{{ $m->payin_wallet }}"
                        name="payin[{{ $m->id }}]" value="0" style="width:120px;"></td>
                    <td class="right muted">{{ number_format($m->payout_wallet,2) }}</td>
                    <td class="right"><input type="number" step="0.01" min="0" max="{{ $m->payout_wallet }}"
                        name="payout[{{ $m->id }}]" value="0" style="width:120px;"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:16px;"><button class="btn" type="submit">Submit settlement</button></div>
    </div>
</form>
@endif
@endsection
