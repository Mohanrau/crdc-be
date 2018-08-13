<?php
namespace App\Policies\Products;

use App\Helpers\Traits\Policy;
use App\Models\Promotions\PromotionFreeItem;
use App\Models\Users\User;
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Request
};

class PromotionFreeItemsPolicy
{
    use HandlesAuthorization, Policy;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * PromotionFreeItemsPolicy constructor.
     *
     * @param PromotionFreeItem $promotionFreeItem
     */
    public function __construct(PromotionFreeItem $promotionFreeItem)
    {
        $this->modelObj = $promotionFreeItem;

        $this->requestObj = Request::all();

        $this->moduleName = 'pwpfoc';

        $this->modelId = 'promo_id';
    }

    /**
     * get country id
     *
     * @param string $section
     * @return mixed
     */
    private function getCountryId(string $section = null)
    {
        return $this->requestObj['country_id'];
    }
}
