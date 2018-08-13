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

class RentalSaleOrderEvents implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $saleId, $stepInput;

    /**
     * Create a new event instance.
     *
     * @param int $saleId
     * @param array $stepInput
     * @return void
     */
    public function __construct(int $saleId, array $stepInput)
    {
        $this->saleId = $saleId;

        $this->stepInput = $stepInput;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|Channel
     */
    public function broadcastOn()
    {
        return new Channel('generate-rental-sale-type-invoice');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'sales.rental.sale.type';
    }
}
