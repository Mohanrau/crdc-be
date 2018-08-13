<?php
namespace App\Events\Enrollments;

use App\Models\Sales\Sale;
use Illuminate\{
    Broadcasting\Channel,
    Broadcasting\PresenceChannel,
    Queue\SerializesModels,
    Broadcasting\PrivateChannel,
    Foundation\Events\Dispatchable,
    Broadcasting\InteractsWithSockets,
    Contracts\Broadcasting\ShouldBroadcast,
    Contracts\Queue\ShouldQueue
};

class EnrollUserEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public
        $sale,
        $uniqueId
    ;

    /**
     * EnrollUserEvent constructor.
     *
     * @param Sale $sale
     * @param string $uniqueId
     */
    public function __construct(Sale $sale, string $uniqueId)
    {
        $this->sale = $sale;

        $this->uniqueId = $uniqueId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('enrollment.'.$this->uniqueId);
    }

    /**
     * get the broadcast event name
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'enrollment.new';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['status' => 'started...'];
    }
}
