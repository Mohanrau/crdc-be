<?php
namespace App\Events\Stockists;

use Illuminate\{
    Broadcasting\Channel,
    Queue\SerializesModels,
    Broadcasting\PrivateChannel,
    Broadcasting\PresenceChannel,
    Foundation\Events\Dispatchable,
    Broadcasting\InteractsWithSockets,
    Contracts\Broadcasting\ShouldBroadcast
};

class ConsignmentOrderReturnEvents implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $consignmentOrderReturnId, $stepInput;

    /**
     * Create a new event instance.
     *
     * @param int $consignmentOrderReturnId
     * @param array $stepInput
     * @return void
     */
    public function __construct(
        int $consignmentOrderReturnId,
        array $stepInput
    )
    {
        $this->consignmentOrderReturnId = $consignmentOrderReturnId;

        $this->stepInput = $stepInput;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|Channel
     */
    public function broadcastOn()
    {
        return new Channel('consignment-order-return-created');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'stockists.consignment-order-return';
    }
}
