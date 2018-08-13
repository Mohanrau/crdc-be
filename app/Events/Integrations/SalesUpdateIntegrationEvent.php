<?php

namespace App\Events\Integrations;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SalesUpdateIntegrationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
        return new Channel('salesUpdate-yonyou-integration-sent');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'salesUpdate-yonyou-integration';
    }
}
