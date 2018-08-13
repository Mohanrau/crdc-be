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

class ConsignmentDepositRefundEvents implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $consignmentDepositRefundId, $stepInput;

    /**
     * Create a new event instance.
     *
     * @param int $consignmentDepositRefundId
     * @param array $stepInput
     * @return void
     */
    public function __construct(
        int $consignmentDepositRefundId,
        array $stepInput
    )
    {
        $this->consignmentDepositRefundId = $consignmentDepositRefundId;

        $this->stepInput = $stepInput;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|Channel
     */
    public function broadcastOn()
    {
        return new Channel('consignment-deposit-refund-created');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'stockists.consignment-deposit-refund';
    }
}
