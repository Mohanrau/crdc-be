<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnrollmentTempNotify extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public
        $name,
        $uniqueCode;

    /**
     * EnrollmentTempNotify constructor.
     *
     * @param string $name
     * @param string $uniqueCode
     */
    public function __construct(string $name, string $uniqueCode)
    {
        $this->name = $name;

        $this->uniqueCode = $uniqueCode;
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
            ->markdown('emails.enrollments.tempdata');
    }
}
