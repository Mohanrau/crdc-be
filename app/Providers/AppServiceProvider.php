<?php
namespace App\Providers;

use Illuminate\{
    Support\Facades\Auth,
    Support\Facades\Schema,
    Support\ServiceProvider
};
use App\{
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
    }
}
