<?php

namespace App\Events\Integrations;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CollectionIntegrationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $collectionId, $saleType;

    /**
     * Create a new event instance.
     * @param  integer $collectionId
     * @param string $saleType
     * @return void
     */
    public function __construct(
        int $collectionId,
        string $saleType
    )
    {
        $this->collectionId = $collectionId;
        $this->saleType = $saleType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('consignmentDepositRefund-yonyou-integration-sent');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'consignmentDepositRefund-yonyou-integration';
    }
}
