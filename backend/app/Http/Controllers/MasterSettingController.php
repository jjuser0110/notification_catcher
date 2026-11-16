<?php

namespace App\Http\Controllers;

use App\Models\MasterSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterSettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', ['settings' => MasterSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payin_cost_rate' => ['required', 'numeric', 'between:0,100'],
            'payin_default_rate' => ['required', 'numeric', 'between:0,100'],
            'payin_max_rate' => ['required', 'numeric', 'between:0,100'],
            'payout_cost_rate' => ['required', 'numeric', 'between:0,100'],
            'payout_default_rate' => ['required', 'numeric', 'between:0,100'],
            'payout_max_rate' => ['required', 'numeric', 'between:0,100'],
        ]);

        MasterSetting::current()->update($data);

        return back()->with('ok', 'Master rate settings saved.');
    }
}
