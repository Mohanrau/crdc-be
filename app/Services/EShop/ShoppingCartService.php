<?php
namespace App\Services\EShop;

use App\Interfaces\Sales\SaleProductKittingInterface;
use App\Models\{
    Users\User
};
use App\Helpers\ValueObjects\StatusMessage;
use App\Services\Sales\CommissionService;

class ShoppingCartService
{
    private
        $commissionService
    ;

    /**
     * ShoppingCartService constructor.
     *
     * @param CommissionService $commissionService
     */
    public function __construct (CommissionService $commissionService) {
        $this->commissionService = $commissionService;
    }

    /**
     * Check if the cart meets checkout criteria
     *
     * @param SaleProductKittingInterface $productKitting
     * @param $user
     * @return StatusMessage
     * @throws \App\Exceptions\Masters\InvalidSaleTypeIdException
     */
    public function processCheckout (SaleProductKittingInterface $productKitting, User $user) : StatusMessage
    {
        return $this->commissionService->userMinimumCvRequirement($productKitting, $user);
        // Check if user is active
    }
}