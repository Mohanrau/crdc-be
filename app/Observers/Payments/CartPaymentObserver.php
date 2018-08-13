<?php
namespace App\Observers\Payments;

use App\Models\{
    Payments\Payment,
    Sales\GuestSale,
    Users\Guest
};
use App\Interfaces\{
    Enrollments\EnrollmentInterface,
    Shop\ShopCartInterface
};
use App\Helpers\Classes\UserIdentifier;
use Auth;

class CartPaymentObserver
{
    private
        $enrollmentRepository,
        $shopCartRepository
    ;

    /**
     * Guest Sales Observer constructor.
     * @param EnrollmentInterface $enrollmentRepository
     * @param ShopCartInterface $shopCartRepository
     */
    public function __construct(
        EnrollmentInterface $enrollmentRepository,
        ShopCartInterface $shopCartRepository
    )
    {
        $this->enrollmentRepository = $enrollmentRepository;

        $this->shopCartRepository = $shopCartRepository;
    }

    /**
     * Clear the car on successful sales
     *
     * @param Payment $payment
     */
    public function saved(Payment $payment)
    {
        // payment status is successful
        if ($payment->status === 1) {
            /** @var \App\Models\Sales\Sale $sale */
            if ($sale = $payment->sale()->with('user')->first()) {
                if ($guestToken = GuestSale::where('sale_id', $sale->id)->first()) {
                    $this->shopCartRepository
                        ->userCartClear(UserIdentifier::mockIdentity(
                            $guestToken->guest_unique_id,
                            new Guest()
                        ));
                } else {
                    $this->shopCartRepository->userCartClear($sale->user->identifier());
                }
            }
        }
    }
}