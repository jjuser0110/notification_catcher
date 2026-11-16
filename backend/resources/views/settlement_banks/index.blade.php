@extends('layouts.sidebar')
@section('title', 'Settlement Banks')

@section('content')
<div class="page-head"><h1>Settlement Banks</h1></div>

<form method="POST" action="{{ route('settlement_banks.store') }}" class="panel" style="max-width:640px;">
    @csrf
    <h2>Add settlement bank</h2>
    <div class="row">
        <div class="field"><label>Bank</label>
            <select name="bank_id" required>
                @foreach($banksList as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </select></div>
        <div class="field"><label>Account holder name</label>
            <input type="text" name="fullname" required></div>
    </div>
    <div class="row" style="align-items:flex-end;">
        <div class="field"><label>Account number</label>
            <input type="text" name="account_no" required></div>
        <div class="field" style="flex:0;"><button class="btn" type="submit">Add</button></div>
    </div>
</form>

@if($settlementBanks->isEmpty())
    <div class="panel muted" style="text-align:center;">No settlement banks yet.</div>
@else
<table>
    <thead><tr><th>Bank</th><th>Account name</th><th>Account no</th><th>Status</th><th class="right"></th></tr></thead>
    <tbody>
        @foreach($settlementBanks as $sb)
        <tr>
            <td>{{ $sb->bank->name }}</td>
            <td>{{ $sb->fullname }}</td>
            <td>{{ $sb->account_no }}</td>
            <td><span class="tag {{ $sb->is_active ? 'on' : 'off' }}">{{ $sb->is_active ? 'Active' : 'Off' }}</span></td>
            <td class="right">
                <form method="POST" action="{{ route('settlement_banks.destroy', $sb) }}" class="inline-form">
                    @csrf @method('DELETE')
                    <button class="btn sm danger" onclick="return confirm('Remove this bank?')">Remove</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
