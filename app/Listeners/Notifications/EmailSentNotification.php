<?php
namespace App\Listeners\Notifications;

use App\Models\Notifications\Tracking;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;

class EmailSentNotification
{
    private $trackingObj;

    /**
     * EmailSentNotification constructor.
     *
     * @param Tracking $tracking
     */
    public function __construct(Tracking $tracking)
    {
        $this->trackingObj = $tracking;
    }

    /**
     * Handle the event.
     *
     * @param  NotificationSent  $event
     * @return void
     */
    public function handle(NotificationSent $event)
    {
        if (Auth::check()){

            if ($event->channel == 'mail')
            {
                $this->trackingObj->create([
                    'user_id' => optional($event->notification->userObj)->id,
                    'cc' => $event->notification->cc,
                    'bcc' => $event->notification->bcc,
                    'from' => env('MAIL_USERNAME'),
                    'to' => $event->notification->userObj->email,
                    'channel' => $event->channel,
                    'subject' => $event->notification->subject,
                    'body' =>
                        $event->notification->greeting. optional($event->notification->userObj)->name .
                        $event->notification->body
                ]);
            }
        }

    }
}
