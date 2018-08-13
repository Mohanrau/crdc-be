<?php
namespace App\Providers;

use Illuminate\{
    Support\Facades\Auth,
    Support\Facades\Schema,
    Support\ServiceProvider
};
use App\{
    Models\Sales\Sale,
    Models\Payments\Payment,
    Observers\Sales\GuestSaleObserver,
    Observers\Payments\CartPaymentObserver,
    Services\EShop\ShoppingCartService,
    Services\Sales\CommissionService,
    Helpers\Classes\Master,
    Interfaces\Masters\MasterInterface
};
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        /**
         * For all the observers
         */
        Sale::observe(GuestSaleObserver::class);

        Payment::observe(CartPaymentObserver::class);

        //check horizon
        Horizon::auth(function ($request) {
            Auth::guard('web')->loginUsingId(1);

            if (Auth::guard('web')->check()){
                return true;
            }

            return false;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Master Facade
        $this->app->singleton(Master::class, function ($app) {
            return new Master($app->make(MasterInterface::class));
        });

        // Sales Services
        $this->app->singleton(CommissionService::class, CommissionService::class);

        // EShop Services
        $this->app->singleton(ShoppingCartService::class, ShoppingCartService::class);
    }
}
