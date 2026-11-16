<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\SettlementBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettlementBankController extends Controller
{
    public function index(Request $request): View
    {
        return view('settlement_banks.index', [
            'banksList' => Bank::where('is_active', true)->orderBy('name')->get(),
            'settlementBanks' => SettlementBank::with('bank')
                ->where('user_id', $request->user()->id)
                ->latest('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_id' => ['required', 'exists:banks,id'],
            'fullname' => ['required', 'string', 'max:255'],
            'account_no' => ['required', 'string', 'max:100'],
        ]);

        SettlementBank::create($data + [
            'user_id' => $request->user()->id,
            'is_active' => true,
        ]);

        return back()->with('ok', 'Settlement bank added.');
    }

    public function destroy(Request $request, SettlementBank $settlementBank): RedirectResponse
    {
        abort_unless($settlementBank->user_id === $request->user()->id, 403);
        $settlementBank->delete();

        return back()->with('ok', 'Settlement bank removed.');
    }
}
