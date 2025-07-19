<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Item;
use App\Models\User; // Ditambahkan
use Illuminate\Support\Facades\Gate; // Ditambahkan
use App\Observers\ItemObserver;

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
        // Baris ini wajib ada untuk mengaktifkan observer
        Item::observe(ItemObserver::class);

        // ✅ TAMBAHKAN GATE DI SINI
        // Gate ini hanya akan mengizinkan akses jika role user adalah 'Manager Operasional'
        Gate::define('manage-users', function (User $user) {
            return $user->role === 'Manager Operasional';
        });
    }
}
