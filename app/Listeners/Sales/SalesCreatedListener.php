<?php
namespace App\Listeners\Sales;

use App\Events\Sales\SalesCreatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SalesCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param SalesCreatedEvent $event
     */
    public function handle(SalesCreatedEvent $event)
    {
        //
    }
}
