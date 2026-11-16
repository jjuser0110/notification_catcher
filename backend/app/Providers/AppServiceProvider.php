<?php

namespace App\Providers;

use App\Http\Controllers\SettlementController;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bootstrap-style markup matches the .pagination CSS in the layout.
        Paginator::useBootstrapFour();

        // Sidebar needs the pending-settlement badge count for the current user.
        View::composer('layouts.sidebar', function ($view) {
            $user = auth()->user();
            $view->with('sidebarPending', $user ? SettlementController::pendingCountFor($user) : 0);
        });
    }
}
