<?php
namespace App\Observers\Sales;

use App\Models\Sales\{
    Sale,
    GuestSale
};
use App\Interfaces\Enrollments\EnrollmentInterface;
use Auth;

class GuestSaleObserver
{
    private $enrollmentRepository;

    /**
     * Guest Sales Observer constructor.
     *
     * @param EnrollmentInterface $enrollmentRepository
     */
    public function __construct(
        EnrollmentInterface $enrollmentRepository
    )
    {
        $this->enrollmentRepository = $enrollmentRepository;
    }

    /**
     * Map guest to sales on guest sales created
     *
     * @param Sale $sale
     */
    public function created(Sale $sale)
    {
        if (Auth::user()->isGuest()) {
            $guestSale = new GuestSale();

            $guestSale->sale_id = $sale->id;

            /** @var \App\Helpers\Classes\UserIdentifier $identity */
            $identity = Auth::user()->identifier();

            $guestSale->guest_unique_id = $identity->identifier;

            $guestSale->save();

            // update the enrolment temp data if exists
            $this->enrollmentRepository->updateEnrollmentTempSale($sale->id);
        }
    }
}