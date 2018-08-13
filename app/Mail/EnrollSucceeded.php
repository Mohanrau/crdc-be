<?php
namespace App\Mail;

use App\Models\Users\User;
use Illuminate\{
    Bus\Queueable,
    Mail\Mailable,
    Queue\SerializesModels,
    Contracts\Queue\ShouldQueue
};

class EnrollSucceeded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public
        $user,
        $password;

    /**
     * EnrollSucceeded constructor.
     *
     * @param User $user
     * @param string $password
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;

        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(trans('enrollment.email.subject'))
            ->markdown('emails.enrollments.succeeded');
    }
}
