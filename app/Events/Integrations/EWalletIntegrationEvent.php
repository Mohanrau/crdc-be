<?php

namespace App\Events\Integrations;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EWalletIntegrationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eWalletId, $saleType;

    /**
     * Create a new event instance.
     * @param  integer $eWalletId
     * @param string $saleType
     * @return void
     */
    public function __construct(
        int $eWalletId,
        string $saleType
    )
    {
        $this->eWalletId = $eWalletId;
        $this->saleType = $saleType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('eWallet-yonyou-integration-sent');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'eWallet-yonyou-integration';
    }
}
