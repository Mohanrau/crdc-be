<?php
namespace App\Events\Sales;

use Illuminate\{
    Broadcasting\Channel,
    Queue\SerializesModels,
    Broadcasting\PrivateChannel,
    Broadcasting\PresenceChannel,
    Foundation\Events\Dispatchable,
    Broadcasting\InteractsWithSockets,
    Contracts\Broadcasting\ShouldBroadcast
};

class SalesCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sales;

    /**
     * Create a new event instance.
     *
     * @param $salesData
     */
    public function __construct($salesData)
    {
        $this->sales = $salesData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|Channel
     */
    public function broadcastOn()
    {
        return new Channel('sales-created');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'sales.new';
    }
}
