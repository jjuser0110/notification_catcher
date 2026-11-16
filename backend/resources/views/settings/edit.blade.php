@extends('layouts.sidebar')
@section('title', 'Master Settings')

@section('content')
<div class="page-head"><h1>Master Rate Settings</h1></div>

<form method="POST" action="{{ route('master_settings.update') }}" class="panel" style="max-width:560px;">
    @csrf @method('PUT')
    <h2>Payin rates (%)</h2>
    <p class="muted">Band assignable to customers/merchants is between default and max.</p>
    <div class="row">
        <div class="field"><label>Cost</label><input type="number" step="0.01" name="payin_cost_rate" value="{{ $settings->payin_cost_rate }}"></div>
        <div class="field"><label>Default</label><input type="number" step="0.01" name="payin_default_rate" value="{{ $settings->payin_default_rate }}"></div>
        <div class="field"><label>Max</label><input type="number" step="0.01" name="payin_max_rate" value="{{ $settings->payin_max_rate }}"></div>
    </div>

    <h2 style="margin-top:20px;">Payout rates (%)</h2>
    <div class="row">
        <div class="field"><label>Cost</label><input type="number" step="0.01" name="payout_cost_rate" value="{{ $settings->payout_cost_rate }}"></div>
        <div class="field"><label>Default</label><input type="number" step="0.01" name="payout_default_rate" value="{{ $settings->payout_default_rate }}"></div>
        <div class="field"><label>Max</label><input type="number" step="0.01" name="payout_max_rate" value="{{ $settings->payout_max_rate }}"></div>
    </div>

    <div style="margin-top:16px;"><button class="btn" type="submit">Save settings</button></div>
</form>
@endsection
