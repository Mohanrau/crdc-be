<?php
namespace App\Listeners\Enrollments;

use App\{
    Events\Enrollments\EnrollUserEvent,
    Interfaces\Enrollments\EnrollmentInterface
};
use Illuminate\Contracts\Queue\ShouldQueue;

class EnrollUserListener implements ShouldQueue
{
    private
        $enrollmentRepository;

    /**
     * EnrollUserListener constructor.
     *
     * @param EnrollmentInterface $enrollmentInterface
     */
    public function __construct(EnrollmentInterface $enrollmentInterface)
    {
        $this->enrollmentRepository = $enrollmentInterface;
    }

    /**
     * Handle the event.
     *
     * @param EnrollUserEvent $event
     */
    public function handle(EnrollUserEvent $event)
    {
        // Access the order using $event->order...
        $this->enrollmentRepository->processEnrollment($event->sale);
    }
}