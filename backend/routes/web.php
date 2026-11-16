<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterSettingController;
use App\Http\Controllers\PayinController;
use App\Http\Controllers\SettlementBankController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // -- Users (one table, filtered by role) --------------------------------
    Route::get('/users/{role}', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{role}/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users/{role}', [UserController::class, 'store'])->name('users.store');
    Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/user/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');
    Route::post('/user/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');

    // -- Payins -------------------------------------------------------------
    Route::get('/payins', [PayinController::class, 'index'])->name('payins.index');
    Route::get('/payin/{payin}', [PayinController::class, 'show'])->name('payins.show');
    Route::post('/payin/{payin}/approve', [PayinController::class, 'approve'])
        ->middleware('role:superadmin,admin,staff')->name('payins.approve');

    // -- Settlements --------------------------------------------------------
    Route::get('/settlements', [SettlementController::class, 'index'])->name('settlements.index');
    Route::get('/settlements/create', [SettlementController::class, 'create'])->name('settlements.create');
    Route::post('/settlements', [SettlementController::class, 'store'])->name('settlements.store');
    Route::post('/settlement/{settlement}/approve', [SettlementController::class, 'approve'])
        ->middleware('role:superadmin,admin,staff')->name('settlements.approve');
    Route::post('/settlement/{settlement}/reject', [SettlementController::class, 'reject'])
        ->middleware('role:superadmin,admin,staff')->name('settlements.reject');

    // Settlement banks (agent's own payout destinations)
    Route::get('/settlement-banks', [SettlementBankController::class, 'index'])->name('settlement_banks.index');
    Route::post('/settlement-banks', [SettlementBankController::class, 'store'])->name('settlement_banks.store');
    Route::delete('/settlement-bank/{settlementBank}', [SettlementBankController::class, 'destroy'])->name('settlement_banks.destroy');

    // -- Staff-side only: banks, bank accounts, master settings -------------
    Route::middleware('role:superadmin,admin,staff')->group(function () {
        Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
        Route::post('/banks', [BankController::class, 'store'])->name('banks.store');
        Route::put('/bank/{bank}', [BankController::class, 'update'])->name('banks.update');

        Route::get('/bank-settings', [BankSettingController::class, 'index'])->name('bank_settings.index');
        Route::post('/bank-settings', [BankSettingController::class, 'store'])->name('bank_settings.store');
        Route::put('/bank-setting/{bankSetting}', [BankSettingController::class, 'update'])->name('bank_settings.update');
    });

    Route::middleware('role:superadmin')->group(function () {
        Route::get('/master-settings', [MasterSettingController::class, 'edit'])->name('master_settings.edit');
        Route::put('/master-settings', [MasterSettingController::class, 'update'])->name('master_settings.update');
    });

    // -- Notification Catcher (phones + messages) ---------------------------
    Route::get('/devices', [DashboardController::class, 'devices'])->name('devices');
    Route::get('/messages', [DashboardController::class, 'messages'])->name('messages');
});
