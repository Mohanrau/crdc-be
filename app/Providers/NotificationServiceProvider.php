<?php
namespace App\Providers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\ServiceProvider;
use App\Notifications\GeneralNotification;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('general-notification', function ($app){
           return new GeneralNotification();
        });
    }
}
