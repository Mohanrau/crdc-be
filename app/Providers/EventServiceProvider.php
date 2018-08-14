<?php
namespace App\Providers;

use App\Listeners\{
    Notifications\EmailSentNotification,
    Token\GuestTokenCreatedListener
};
use Illuminate\Notifications\Events\NotificationSent;
use Laravel\Passport\Events\AccessTokenCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        //notification events-------------------------------------------------------------------------------------------
        NotificationSent::class => [EmailSentNotification::class],

        //user events---------------------------------------------------------------------------------------------------
        AccessTokenCreated::class => [GuestTokenCreatedListener::class],

    ];

    protected $subscribe = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
