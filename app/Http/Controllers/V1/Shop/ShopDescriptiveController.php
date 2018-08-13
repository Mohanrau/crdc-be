<?php
namespace App\Http\Controllers\V1\Shop;

use App\{
    Http\Controllers\Controller,
    Interfaces\Products\ProductCategoryInterface,
    Interfaces\Sales\SaleInterface
};
use Auth;

class ShopDescriptiveController extends Controller
{
    private
        $obj,
        $saleRepository
    ;

    /**
     * ShopDescriptiveController constructor.
     *
     * @param ProductCategoryInterface $productCategoryObj
     */
    public function __construct(ProductCategoryInterface $productCategoryObj, SaleInterface $saleRepository)
    {
        $this->middleware('auth');

        $this->obj = $productCategoryObj;

        $this->saleRepository = $saleRepository;
    }

    /**
     * Returns the list of categories to display in eshop
     *
     * @return \Symfony\Component\HttpFoundation\Response of \App\Models\Products\ProductCategory[]
     */
    public function categories() {
        return response($this->obj->getShopCategories());
    }

    /**
     * Logged in members current CW details
     *
     * @return array
     * [
     *   'ampCvToUpgradeEachBaLevel' = <amount>,
     *   'upgradeAmpCv' = <amount>,
     *   'baUpgradeCv' = <amount>,
     *   'memberUpgradeCv' = <amount>,
     *   'currentCwId' = <id>,
     *   'currentCwLog' = <{@see \App\Models\Members\MemberEnrollmentRankUpgradeLog}>
     * ]
     */
    public function memberCurrentCwDetails() {
        return response($this->saleRepository->currentCwUpgradeCvForUser(Auth::id()));
    }
}