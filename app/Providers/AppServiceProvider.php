<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Medicament;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // Gates d'autorisation RBAC
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('gerer-medicaments', fn (User $user) => $user->hasRole(Role::Admin, Role::Pharmacien));
        Gate::define('supprimer-vente', fn (User $user) => $user->isAdmin());
        Gate::define('acceder-statistiques', fn (User $user) => $user->hasRole(Role::Admin, Role::Pharmacien));
        Gate::define('gerer-caisse', fn (User $user) => $user->hasRole(Role::Admin, Role::Pharmacien, Role::Caissier));

        // Alertes de stock pour la navigation latérale
        View::composer('app.menu', function ($view) {
            if (auth()->check()) {
                $view->with('alertesStock', [
                    'faible' => Medicament::stockFaible()->count(),
                    'rupture' => Medicament::enRupture()->count(),
                    'expire' => Medicament::procheExpiration()->count(),
                ]);
            }
        });
    }
}
