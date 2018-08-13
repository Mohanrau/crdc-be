<?php
namespace App\Notifications\Users;

use App\Models\Users\User;
use Illuminate\{
    Bus\Queueable,
    Notifications\Notification,
    Contracts\Queue\ShouldQueue,
    Notifications\Messages\MailMessage
};

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $userObj;

    /**
     * WelcomeNotification constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->userObj = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            //->cc($this->cc)
            //->bcc($this->bcc)
            ->subject(trans('email.welcome.subject'))
            ->line(trans('email.welcome.dear', ['salutation'=>'Mr/Mrs', 'name'=>$this->userObj->name]))
            ->line(trans('email.welcome.body'))
            ->action(trans('email.welcome.visit'), url('/'))
            ->line(trans('email.welcome.footer'));
    }

}
