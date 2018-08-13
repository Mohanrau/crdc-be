<?php

namespace App\Events\Integrations;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SalesReceiptIntegrationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $saleId, $saleType;

    /**
     * Create a new event instance.
     *
     * @param int $saleId
     * @param string $saleType
     * @return void
     */
    public function __construct(
        int $saleId,
        string $saleType
    )
    {
        $this->saleId = $saleId;
        $this->saleType = $saleType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('salesreceipt-yonyou-integration-sent');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'salesreceipt-yonyou-integration';
    }
}
