<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankSettingController extends Controller
{
    public function index(): View
    {
        return view('bank_settings.index', [
            'settings' => BankSetting::with('bank', 'creator')->latest('id')->paginate(25),
            'banks' => Bank::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        BankSetting::create($data);

        return back()->with('ok', 'Bank account added.');
    }

    public function update(Request $request, BankSetting $bankSetting): RedirectResponse
    {
        $bankSetting->update($this->validateData($request));

        return back()->with('ok', 'Bank account updated.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'bank_id' => ['required', 'exists:banks,id'],
            'account_no' => ['required', 'string', 'max:100'],
            'fullname' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return [
            'bank_id' => $data['bank_id'],
            'account_no' => $data['account_no'],
            'fullname' => $data['fullname'],
            'amount' => $data['amount'] ?? 0,
            'for_payin' => $request->boolean('for_payin'),
            'for_payout' => $request->boolean('for_payout'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
