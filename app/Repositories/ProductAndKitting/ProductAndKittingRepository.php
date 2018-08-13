<?php
namespace App\Repositories\ProductAndKitting;

use App\Interfaces\{
    Kitting\KittingInterface,
    Masters\MasterDataInterface,
    Masters\MasterInterface,
    Members\MemberInterface,
    ProductAndKitting\ProductAndKittingInterface,
    Products\ProductInterface
};
use App\Models\{
    Campaigns\EsacPromotion,
    Campaigns\EsacVoucher,
    Locations\Location,
    Stockists\Stockist,
    Stockists\StockistConsignmentProduct,
    Enrollments\EnrollmentTypes
};

class ProductAndKittingRepository implements ProductAndKittingInterface
{
    private
        $productRepositoryObj,
        $kittingRepositoryObj,
        $memberRepositoryObj,
        $masterRepositoryObj,
        $masterDataRepositoryObj,
        $esacVoucherObj,
        $esacPromotionObj,
        $locationObj,
        $stockistObj,
        $stockistConsignmentProductObj,
        $enrollmentTypesObj
    ;

    /**
     * ProductAndKittingRepository constructor.
     *
     * @param ProductInterface $productInterface
     * @param KittingInterface $kittingInterface
     * @param MemberInterface $memberInterface
     * @param MasterInterface $masterInterface
     * @param MasterDataInterface $masterDataInterface
     * @param EsacVoucher $esacVoucher
     * @param EsacPromotion $esacPromotion
     * @param Location $location
     * @param Stockist $stockist
     * @param StockistConsignmentProduct $stockistConsignmentProduct
     * @param EnrollmentTypes $enrollmentTypes
     */
    public function __construct(
        ProductInterface $productInterface,
        KittingInterface $kittingInterface,
        MemberInterface $memberInterface,
        MasterInterface $masterInterface,
        MasterDataInterface $masterDataInterface,
        EsacVoucher $esacVoucher,
        EsacPromotion $esacPromotion,
        Location $location,
        Stockist $stockist,
        StockistConsignmentProduct $stockistConsignmentProduct,
        EnrollmentTypes $enrollmentTypes
    )
    {
        $this->productRepositoryObj = $productInterface;

        $this->kittingRepositoryObj = $kittingInterface;

        $this->memberRepositoryObj = $memberInterface;

        $this->masterRepositoryObj = $masterInterface;

        $this->masterDataRepositoryObj = $masterDataInterface;

        $this->locationObj = $location;

        $this->stockistObj = $stockist;

        $this->esacPromotionObj = $esacPromotion;

        $this->esacVoucherObj = $esacVoucher;

        $this->stockistConsignmentProductObj = $stockistConsignmentProduct;

        $this->enrollmentTypesObj = $enrollmentTypes;
    }

    /**
     * search products or kitting for a given member and country_id
     *
     * @param int $userId
     * @param int $countryId
     * @param int $locationId
     * @param string $text
     * @param array $esacVouchers
     * @param array $saleTypes
     * @param bool $isConsignmentReturn
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @param bool $mixed
     * @return mixed
     */
    public function searchProductsAndKitting(
        int $userId,
        int $countryId,
        int $locationId,
        string $text = '',
        array $esacVouchers = null,
        array $saleTypes = [],
        bool $isConsignmentReturn = false,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0,
        bool $mixed = false
    )
    {
        $member = $this->memberRepositoryObj->find($userId, ['enrollmentRank']);

        $memberSalesTypes = json_decode($member->enrollmentRank->sales_types);

        $master = $this->masterRepositoryObj
            ->getMasterByKey($memberSalesTypes->key, ['id']);

        $memberSalesTypesIds =  $this->masterDataRepositoryObj
            ->findByKeys($master->id, 'title',$memberSalesTypes->values,['id'])
            ->pluck('id')
            ->toArray();

        if (!empty($saleTypes) && count($saleTypes) > 0){
            $memberSalesTypesIds = $saleTypes;
        }

        $includeCategories = $includeProducts = $includeKittings = $excludeProducts = $excludeKittings = null;

        $isEsac = false;

        if (isset($esacVouchers) && count($esacVouchers) > 0) {
            $includeCategories = $includeProducts = $includeKittings = $excludeProducts = $excludeKittings = [];

            $isEsac = true;

            foreach ($esacVouchers as $esacVoucherId) {
                $esacVoucher = $this
                    ->esacVoucherObj
                    ->findOrFail($esacVoucherId);

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
        }

        if($isConsignmentReturn){

            $location = $this->locationObj->find($locationId);

            //Get Stockist ID
            $stockist = $this->stockistObj
                ->where('stockist_number', $location->code)
                ->first();

            if($stockist){
                $includeProducts = $this->stockistConsignmentProductObj
                    ->where('stockist_id', $stockist->id)
                    ->where('available_quantity', '>', 0)
                    ->pluck('product_id')
                    ->toArray();
            }
        }

        //check if the request want the response to be mixed array
        if ($mixed){
            //get the eligible products for this member[salesTypes]
            $products = $this->productRepositoryObj
                ->searchProducts(
                    $countryId,
                    0,
                    $text,
                    $locationId,
                    1,
                    true ,
                    $memberSalesTypesIds,
                    $includeCategories,
                    $includeProducts,
                    $excludeProducts,
                    false,
                    0
                );

            //get the eligible kitting for this member[salesTypes]
            $kitting = $this->kittingRepositoryObj
                ->getKittingByFilters(
                    $countryId,
                    $isEsac,
                    $text,
                    '',
                    $memberSalesTypesIds,
                    $locationId,
                    $includeCategories,
                    $includeKittings,
                    $excludeKittings,
                    '',
                    1,
                    0
                );

        }else{
            //get the eligible products for this member[salesTypes]
            $products = $this->productRepositoryObj
                ->searchProducts(
                    $countryId,
                    0,
                    $text,
                    $locationId,
                    1,
                    true ,
                    $memberSalesTypesIds,
                    $includeCategories,
                    $includeProducts,
                    $excludeProducts,
                    false,
                    0
                );

            //get the eligible kitting for this member[salesTypes]
            $kitting = $this->kittingRepositoryObj
                ->getKittingByFilters(
                    $countryId,
                    $isEsac,
                    $text,
                    '',
                    $memberSalesTypesIds,
                    $locationId,
                    $includeCategories,
                    $includeKittings,
                    $excludeKittings,
                    '',
                    1,
                    $paginate,
                    $orderBy,
                    $orderMethod,
                    $offset
                );
        }

        //attach the effective price for each product
        $this->attachProductActivePrice($products, $countryId, $locationId);

        //attach kitting price for each kitting
        $this->attachKittingPrice($kitting);

        //attach image link (for esac usage)
        if (isset($esacVouchers) && count($esacVouchers) > 0) {
            if ($products->count() > 0) {
                collect($products['data'])->each(function ($item) use ($countryId) {
                    $productData = $this->productRepositoryObj
                        ->find($item['id']);

                    $image = $productData->productImages($countryId)
                        ->default()
                        ->first();

                    if ($image == null) {
                        $image = $productData->productImages($countryId)
                            ->first();
                    }

                    if ($image == null) {
                        $item['image_link'] = '';
                    }
                    else {
                        $item['image_link'] = $image->image_link;
                    }
                });
            }

            if ($kitting->count() > 0) {
                collect($kitting['data'])->each(function ($item) {
                    $kittingData = $this->kittingRepositoryObj
                        ->find($item->id);

                    $image = $kittingData->kittingImages()
                        ->default()
                        ->first();

                    if ($image == null) {
                        $image = $kittingData->kittingImages()
                            ->first();
                    }

                    if ($image == null) {
                        $item->image_link = '';
                    }
                    else {
                        $item->image_link = $image->image_link;
                    }
                });
            }
        }

        if ($mixed){
            $pageNumber = ($offset == 0) ? $offset + 1 : ($offset/ $paginate)+1;

            return [
                'total' =>  $products['total'] + $kitting['total'],
                'data' =>
                    $products['data']
                        ->concat($kitting['data'])
                        ->forPage($pageNumber,$paginate)
                        ->shuffle()
            ];

        }else{
            return [
                "products" => $products,
                "kitting" => $kitting
            ];
        }
    }

    /**
     * search for available products and kitting for the enrollment
     *
     * @param int $countryId
     * @param int $locationId
     * @param int $enrollmentTypeId
     * @param string $text
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return array|mixed
     */
    public function searchProductsAndKittingEnrollment(
        int $countryId,
        int $locationId,
        int $enrollmentTypeId,
        string $text,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $enrollmentType = $this->enrollmentTypesObj->find($enrollmentTypeId);        

        $salesTypes = json_decode($enrollmentType->sale_types);

        $salesTypesIds =  $this->masterDataRepositoryObj
            ->findByKeys(
                $this->masterRepositoryObj->getMasterByKey('sale_types')->id,
                'title',
                $salesTypes,
                ['id']
            )
            ->pluck('id')
            ->toArray();

        $includeCategories = $includeProducts = $includeKitting = $excludeProducts = $excludeKitting = null;

        //get the eligible products for this member[salesTypes]
        $products = $this->productRepositoryObj->searchProducts(
            $countryId,
            $categoryId = 0,
            $text,
            $locationId,
            $active = 1,
            $checkDates = true ,
            $salesTypesIds,
            $includeCategories,
            $includeProducts,
            $excludeProducts,
            $exactSearch = false,
            $pagination = 0
        );

        //get the eligible kitting for this member[salesTypes]
        $kitting = $this->kittingRepositoryObj->getKittingByFilters(
            $countryId,
            $isEsac = false,
            $text,
            $productSku = '',
            $salesTypesIds,
            $locationId,
            $includeCategories,
            $includeKitting,
            $excludeKitting,
            '',
            $active=  1,
            $pagination = 0
        );

        //attach the effective price for each product
        $this->attachProductActivePrice($products, $countryId, $locationId);

        //attach kitting price for each kitting
        $this->attachKittingPrice($kitting);

        $pageNumber = ($offset == 0) ? $offset + 1 : ($offset/ $paginate)+1;
        
        return [
            'total' =>  $products['total'] + $kitting['total'],
            'data' =>
                $products['data']
                    ->concat($kitting['data'])
                    ->forPage($pageNumber,$paginate)
                    ->shuffle()
        ];
    }

    /**
     * attach products with the active price
     *
     * @param $products
     * @param int $countryId
     * @param int $locationId
     */
    private function attachProductActivePrice(&$products, int $countryId, int $locationId)
    {
        if($products->count() > 0){
            collect($products['data'])->each(function ($item) use ($countryId,$locationId){
                $locationArray[] = $locationId;

                $item['price'] = $this->productRepositoryObj
                    ->productEffectivePricing($countryId, $item['id'], $locationArray);

                $item['is_kitting'] = 0;
            });
        }
    }

    /**
     * attach prices object for the kitting
     *
     * @param $kitting
     */
    private function attachKittingPrice(&$kitting)
    {
        if($kitting->count() > 0){
            collect($kitting['data'])->each(function ($item){
                $kittingData = $this->kittingRepositoryObj->find($item->id);

                $item->price = $kittingData->kittingPrice()->first();

                $item->is_kitting = 1;
            });
        }
    }
}