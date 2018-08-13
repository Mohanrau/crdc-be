<?php
namespace App\Providers;

use App\Events\{
    Enrollments\EnrollUserEvent,
    Sales\RentalSaleOrderEvents,
    Sales\SaleCancellationEvents,
    Sales\SalesCreatedEvent,
    Stockists\ConsignmentDepositRefundEvents,
    Stockists\ConsignmentOrderReturnEvents
};
use App\Listeners\{
    Enrollments\EnrollUserListener,
    Notifications\EmailSentNotification,
    Sales\RentalSaleOrderListeners,
    Sales\SaleCancellationListeners,
    Sales\SalesCreatedListener,
    Stockists\ConsignmentDepositRefundListener,
    Stockists\ConsignmentOrderReturnListener,
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

        //sales events--------------------------------------------------------------------------------------------------
        SalesCreatedEvent::class => [SalesCreatedListener::class],

        RentalSaleOrderEvents::class => [RentalSaleOrderListeners::class],

        SaleCancellationEvents::class => [SaleCancellationListeners::class],

        //consignment events--------------------------------------------------------------------------------------------
        ConsignmentDepositRefundEvents::class => [ConsignmentDepositRefundListener::class],

        ConsignmentOrderReturnEvents::class => [ConsignmentOrderReturnListener::class],

        //user events---------------------------------------------------------------------------------------------------
        AccessTokenCreated::class => [GuestTokenCreatedListener::class],

        //enrollment events---------------------------------------------------------------------------------------------
        EnrollUserEvent::class => [EnrollUserListener::class],
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
