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

class SaleCancellationEvents implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $saleCancellationId, $stepInput;

    /**
     * Create a new event instance.
     *
     * @param int $saleCancellationId
     * @param array $stepInput
     * @return void
     */
    public function __construct(int $saleCancellationId, array $stepInput)
    {
        $this->saleCancellationId = $saleCancellationId;

        $this->stepInput = $stepInput;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|Channel
     */
    public function broadcastOn()
    {
        return new Channel('sales-cancelled');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'sales.cancellation';
    }
}
