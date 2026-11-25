@extends('layouts.sidebar')
@section('title', 'Banks')

@section('content')
<div class="page-head"><h1>Banks</h1></div>

<form method="POST" action="{{ route('banks.store') }}" class="panel" style="max-width:520px;">
    @csrf
    <h2>Add bank</h2>
    <div class="row" style="align-items:flex-end;">
        <div class="field"><label>Name</label><input type="text" name="name" required></div>
        <div class="field"><label>Code (optional)</label><input type="text" name="code"></div>
        <div class="field" style="flex:0;"><button class="btn" type="submit">Add</button></div>
    </div>
</form>

<table>
    <thead><tr><th>Name</th><th>Code</th><th class="right">Accounts</th><th>Status</th><th class="right"></th></tr></thead>
    <tbody>
        @foreach($banks as $bank)
        <tr>
            <form method="POST" action="{{ route('banks.update', $bank) }}">@csrf @method('PUT')
            <td data-label="Name"><input type="text" name="name" value="{{ $bank->name }}" style="width:180px;"></td>
            <td data-label="Code"><input type="text" name="code" value="{{ $bank->code }}" style="width:90px;"></td>
            <td data-label="Accounts" class="right muted">{{ $bank->bank_settings_count }}</td>
            <td data-label="Status"><label style="display:flex;align-items:center;gap:6px;margin:0;">
                <input type="checkbox" name="is_active" value="1" @checked($bank->is_active) style="width:auto;"> Active</label></td>
            <td class="right actions"><button class="btn sm">Save</button></td>
            </form>
        </tr>
        @endforeach
    </tbody>
</table>
<div>{{ $banks->links() }}</div>
@endsection
