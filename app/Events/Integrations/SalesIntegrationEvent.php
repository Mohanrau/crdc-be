<?php

namespace App\Events\Integrations;

use Illuminate\{
    Broadcasting\Channel,
    Queue\SerializesModels,
    Broadcasting\PrivateChannel,
    Broadcasting\PresenceChannel,
    Foundation\Events\Dispatchable,
    Broadcasting\InteractsWithSockets,
    Contracts\Broadcasting\ShouldBroadcast
};

class SalesIntegrationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $saleId, $saleType, $release;

    /**
     * Create a new event instance.
     *
     * @param int $saleId
     * @param string $saleType
     * @param bool $stockRelease
     * @return void
     */
    public function __construct(
        int $saleId,
        string $saleType,
        bool  $stockRelease
    )
    {
        $this->saleId = $saleId;
        $this->saleType = $saleType;
        $this->release = $stockRelease;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|Channel
     */
    public function broadcastOn()
    {
        return new Channel('sales-yonyou-integration-sent');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'sales-yonyou-integration';
    }
}
