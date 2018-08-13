<?php
namespace App\Notifications\Users;

use App\Models\Users\User;
use Illuminate\{
    Bus\Queueable,
    Notifications\Notification,
    Contracts\Queue\ShouldQueue,
    Notifications\Messages\MailMessage
};

class StaffWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public
        $userObj,
        $plainPassword
    ;

    /**
     * StaffWelcomeNotification constructor.
     *
     * @param User $user
     * @param string $plainPassword
     */
    public function __construct(User $user, string $plainPassword)
    {
        $this->userObj = $user;

        $this->plainPassword = $plainPassword;
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
            ->subject(trans('email.staff_welcome.subject'))
            ->line(trans('email.staff_welcome.dear', ['salutation'=>'Mr/Mrs', 'name'=>$this->userObj->name]))
            ->line(trans('email.staff_welcome.body'))
            ->line(trans('email.staff_welcome.credentials'))
            ->line(trans('email.staff_welcome.email', ['email' => $this->userObj->email]))
            ->line(trans('email.staff_welcome.password', ['password' => $this->plainPassword]))
            ->action(trans('email.staff_welcome.visit'), url('/'))
            ->line(trans('email.staff_welcome.footer'));
    }
}
