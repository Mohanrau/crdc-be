<?php
namespace App\Rules\Campaign;

use App\Models\Campaigns\EsacVoucher;
use App\Models\Campaigns\EsacPromotion;
use App\Models\Locations\Country;
use App\Models\Kitting\KittingProduct;
use Illuminate\Contracts\Validation\Rule;

class EsacVoucherRedemption implements Rule
{
    private $esacVoucherObj, 
        $esacPromotionObj, 
        $countryObj, 
        $kittingProductObj,  
        $errorMessage;

    /**
     * EsacVoucherRedemption constructor
     * 
     * @param EsacVoucher $esacVoucher
     * @param EsacPromotion $esacPromotion
     * @param Country $country
     * @param KittingProduct $kittingProduct
     */
    public function __construct(
        EsacVoucher $esacVoucher,
        EsacPromotion $esacPromotion,
        Country $country,
        KittingProduct $kittingProduct) 
    {
        $this->esacVoucherObj = $esacVoucher;

        $this->esacPromotionObj = $esacPromotion;
        
        $this->countryObj = $country;
        
        $this->kittingProductObj = $kittingProduct;
    }

    /**
     * Determine if the validation rule passes
     * 
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!isset($value['is_esac_redemption']) || $value['is_esac_redemption'] !== 1) {
            return true;
        }

        $maxPurchaseQty = 0;
        $minPurchaseAmount = 0;
        $totalPurchaseQty = 0;
        $totalPurchaseAmount = floatval($value['order_fees']['total_nmp']);
        $includeCategories = [];
        $includeProducts = [];
        $includeKittings = [];
        $excludeProducts = [];
        $excludeKittings = [];

        foreach ($value['esac_vouchers'] as $esacVoucherId) {
            $esacVoucher = $this
                ->esacVoucherObj
                ->findOrFail($esacVoucherId);

            $maxPurchaseQty += $esacVoucher['max_purchase_qty'];

            $minPurchaseAmount += $esacVoucher['min_purchase_amount'];

            $totalPurchaseAmount += $esacVoucher['voucher_value'];

            $esacPromotion = $this
                ->esacPromotionObj
                ->with([
                    'esacPromotionProductCategories', 
                    'esacPromotionExceptionProducts', 
                    'esacPromotionExceptionKittings', 
                    'esacPromotionProducts', 
                    'esacPromotionKittings'
                ])
                ->findOrFail($esacVoucher['promotion_id']);

            if ($esacPromotion['entitled_by'] === 'P') {
                $includeProducts = $esacPromotion
                    ->esacPromotionProducts
                    ->pluck('id')
                    ->toArray();

                $includeKittings = $esacPromotion
                    ->esacPromotionKittings
                    ->pluck('id')
                    ->toArray();
            }
            else {
                $includeCategories = $esacPromotion
                    ->esacPromotionProductCategories
                    ->pluck('id')
                    ->toArray();

                $excludeProducts =  $esacPromotion
                    ->esacPromotionExceptionProducts
                    ->pluck('id')
                    ->toArray();

                $excludeKittings =  $esacPromotion
                    ->esacPromotionExceptionKittings
                    ->pluck('id')
                    ->toArray();
            }
        }

        if (count($includeCategories) > 0) {
            $productIds = $this
                ->countryObj
                ->find($value['country_id'])
                ->entity()
                ->first()
                ->products()
                ->whereIn('category_id', $includeCategories)
                ->pluck('id')
                ->toArray();
            
            $includeProducts = array_unique(array_merge($includeProducts, $productIds));

            $kittingsIds = $this
                ->kittingProductObj
                ->whereIn('product_id', $productIds)
                ->pluck('kitting_id')
                ->toArray();

            $includeKittings = array_unique(array_merge($includeKittings, $kittingsIds));
        }
         
        foreach ($value['products'] as $product) {
            $totalPurchaseQty += $product['quantity']; 

            if (in_array($product['product_id'], $excludeProducts)) {
                $this->errorMessage = __('message.campaign.exclude-product', []);
                return false;
            }

            if (!in_array($product['product_id'], $includeProducts)) {
                $this->errorMessage = __('message.campaign.include-product', []);
                return false;
            }
        }

        foreach ($value['kittings'] as $kitting) {
            $totalPurchaseQty += $kitting['quantity']; 

            if (in_array($kitting['kitting_id'], $excludeKittings)) {
                $this->errorMessage = __('message.campaign.exclude-kitting', []);
                return false;
            }

            if (!in_array($kitting['kitting_id'], $includeKittings)) {
                $this->errorMessage = __('message.campaign.include-kitting', []);
                return false;
            }
        }

        if ($totalPurchaseQty > $maxPurchaseQty && $maxPurchaseQty > 0) {
            $this->errorMessage = __('message.campaign.max-purchase-qty', []);
            return false;
        }

        if ($totalPurchaseAmount < $minPurchaseAmount) {
            $this->errorMessage = __('message.campaign.min-purchase-amount', []);
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     * 
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
} 
