<?php
namespace App\Notifications\Users;

use Illuminate\{
    Bus\Queueable,
    Notifications\Notification,
    Contracts\Queue\ShouldQueue,
    Notifications\Messages\MailMessage,
    Support\Facades\App
};

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $otpCode, $email, $name, $isMember, $language;

    /**
     * ResetPasswordNotification constructor.
     *
     * @param string $otpCode
     * @param string $email
     * @param string $name
     * @param bool $isMember
     * @param string $language
     */
    public function __construct(
        string $otpCode,
        string $email,
        string $name,
        bool $isMember = false,
        string $language = null
    )
    {
        $this->otpCode = $otpCode;

        $this->email = $email;

        $this->name = $name;

        $this->isMember = $isMember;

        $this->language = is_null($language) ? app()->getLocale() : $language;
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
        $link = $this->isMember ? config('app.member_url') : config('app.backoffice_url');

        return (new MailMessage)
            ->markdown('emails.resetPassword', [
                'name' => $this->name,
                'url' => $link.'/'.$this->otpCode,
                'otp' => $this->otpCode,
                'language' => $this->language
            ]);
    }
}
