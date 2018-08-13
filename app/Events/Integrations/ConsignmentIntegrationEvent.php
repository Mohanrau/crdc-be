<?php

namespace App\Events\Integrations;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ConsignmentIntegrationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $consignmentOrderId, $saleType;

    /**
     * Create a new event instance.
     * @param  integer $consignmentOrderId
     * @param string $saleType
     * @return void
     */
    public function __construct(
        int $consignmentOrderId,
        string $saleType
    )
    {
        $this->consignmentOrderId = $consignmentOrderId;
        $this->saleType = $saleType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('consignmentOrderReturn-yonyou-integration-sent');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'consignmentOrderReturn-yonyou-integration';
    }
}
