<?php

namespace App\Providers;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Territory;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\DistributorPolicy;
use App\Policies\OutletPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\TerritoryPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Contracts\MapProviderInterface::class,
            \App\Services\Providers\GoogleMapsProvider::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(User::class, DistributorPolicy::class);
        Gate::policy(Territory::class, TerritoryPolicy::class);
        Gate::policy(Outlet::class, OutletPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
    }
}
