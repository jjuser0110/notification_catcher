<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankController extends Controller
{
    public function index(): View
    {
        return view('banks.index', ['banks' => Bank::withCount('bankSettings')->orderBy('name')->paginate(10)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('banks', 'name')],
            'code' => ['nullable', 'string', 'max:50'],
        ]);
        Bank::create($data + ['is_active' => true]);

        return back()->with('ok', 'Bank "'.$data['name'].'" added.');
    }

    public function update(Request $request, Bank $bank): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('banks', 'name')->ignore($bank->id)],
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $bank->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('ok', 'Bank updated.');
    }
}
