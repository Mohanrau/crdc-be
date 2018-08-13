<?php
namespace App\Repositories\Promotions;

use App\Models\Promotions\{
    PromotionFreeItem,
    PromotionFreeItemGeneralSetting,
    PromotionFreeItemOptionProducts,
    PromotionFreeItemOption
};
use App\{
    Interfaces\Promotions\PromotionFreeItemsInterface,
    Interfaces\Products\ProductInterface,
    Interfaces\Kitting\KittingInterface,
    Models\Locations\Country,
    Models\Masters\Master,
    Repositories\BaseRepository
};

class PromotionFreeItemRepository extends BaseRepository implements PromotionFreeItemsInterface
{
    private
        $productRepositoryObj,
        $kittingRepositoryObj,
        $countryObj,
        $promoGeneralSettingObj,
        $promoOptionProductsObj,
        $promoOptionObj,
        $masterObj;

    /**
     * PromotionFreeItemRepository constructor.
     *
     * @param PromotionFreeItem $model
     * @param ProductInterface $productInterface
     * @param KittingInterface $kittingInterface
     * @param Country $country
     * @param PromotionFreeItemGeneralSetting $promotionFreeItemGeneralSetting
     * @param PromotionFreeItemOptionProducts $freeItemOptionProducts
     * @param PromotionFreeItemOption $promotionFreeItemOption
     * @param Master $master
     */
    public function __construct(
        PromotionFreeItem $model,
        ProductInterface $productInterface,
        KittingInterface $kittingInterface,
        Country $country,
        PromotionFreeItemGeneralSetting $promotionFreeItemGeneralSetting,
        PromotionFreeItemOptionProducts $freeItemOptionProducts,
        PromotionFreeItemOption $promotionFreeItemOption,
        Master $master
    )
    {
        parent::__construct($model);

        $this->productRepositoryObj = $productInterface;

        $this->kittingRepositoryObj = $kittingInterface;

        $this->countryObj = $country;

        $this->promoGeneralSettingObj = $promotionFreeItemGeneralSetting;

        $this->promoOptionProductsObj = $freeItemOptionProducts;

        $this->promoOptionObj = $promotionFreeItemOption;

        $this->masterObj = $master;
    }

    /**
     * get promotion free item for a given id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get promotion free items by filters
     *
     * @param int $countryId
     * @param string $searchText
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return static
     */
    public function getPromotionFreeItemsByFilters(
        int $countryId,
        string $searchText = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with('promotionType','categories','promotionFreeItemOptionProducts')
            ->where('country_id', $countryId);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        $totalRecords = collect(
            [
                'total' => $this->modelObj->count()
            ]
        );

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get promotionFreeItems Details for a given countryId and promoId(optional)
     *
     * @param int $countryId
     * @param int $promoId
     * @return array|string
     */
    public function promotionFreeItemsDetails(int $countryId, int $promoId)
    {
        $data = '';

        //country info---------------------------------------
        $country = $this->countryObj->find($countryId);

        //currency info
        $currency = $country->currency()->first();

        if ($promoId > 0) {

            $promo = $this->modelObj->findOrFail($promoId);

            //promotion general settings--------------------------------------------------------
            $promoGeneralSetting = $this->promoGeneralSettingObj->getMasters($promo->id);

            $generalTab = [];

            if (count($promoGeneralSetting)>0){
                foreach ($promoGeneralSetting as $master){
                    $generalTab[$master->key] = $this->promoGeneralSettingObj
                        ->where('master_id', $master->masterId)
                        ->where('promo_id', $promo->id)
                        ->pluck('master_data_id')
                        ->toArray();
                }
            }

            //promotion products---------------------------------------------------------------
            $promoProducts = $promo->promotionFreeItemOptionProducts()->get();

            $promoOptions = $promo->promotionOptions()->get();

            $productDataResponse = []; $andProducts = []; $orProducts = [];

            $j = 1;
            foreach ($promoOptions as $option)
            {
                $productArray = explode(',', $option->option_products);

                $i = 1;
                foreach ($productArray as $product)
                {
                    if ($product == '') continue;

                    if ($product != 'or')
                    {
                        $productRecord =  $this->promoOptionProductsObj
                            ->with([
                                'product.productImages' => function ($query) use ($countryId) {
                                    $query->where('country_id', $countryId);
                                    $query->default();
                                    $query->active();
                                },
                                'product'
                            ])
                            ->where('product_id',$product)
                            ->where('promo_id', $promo->id)
                            ->where('option_id', $option->option_id)
                            ->first();

                        //get product name based on country
                        $productName = $productRecord->product->getProductName($countryId);

                        $productRecord->product->name = ($productName) ? $productName->name : $productRecord->product->name;

                        if ($i !=1){
                            $orProducts['option_'.$j]['set_'.$i][] = $productRecord;
                        }else{
                            $andProducts['option_'.$j]['set_'.$i][] = $productRecord;
                        }
                    }
                    else{
                        $i++;
                    }
                }

                $optionID['option_'.$j] = ['option_id' => $option->option_id];

                $productDataResponse = array_merge_recursive($andProducts,$orProducts);

                $productDataResponse = array_merge_recursive($optionID, $productDataResponse);

                $j++;
            }

            $resultArray = [];

            if(count($productDataResponse) > 0) {

                $opt = 1;
                foreach ($productDataResponse as $option)
                {
                    $resultArray[]['option_'.$opt] = $option;

                    $opt++;
                }
            }

            //promo products--------------------------------------------------------------------------------------------
            $productIds = $promo->products()->pluck('id');

            $productIdsData = $promo->products()->get(['id','sku']);

            //promo kitting---------------------------------------------------------------------------------------------
            $kittingIds = $promo->kitting()->pluck('id');

            $kittingIdsData = $promo->kitting()->get(['id','code']);

            //promo category--------------------------------------------------------------------------------------------
            $productCategoryIds = $promo->categories()->pluck('id');

            $productCategoryIdsData = $promo->categories()->get(['id','name']);

            $data = [
                'promo_id' => $promo->id,
                'country_id' => $promo->country_id,
                'name' => $promo->name,
                'start_date' => $promo->start_date,
                'end_date' =>  $promo->end_date,
                'promo_type_id' => $promo->promo_type_id,
                'from_cv_range' => $promo->from_cv_range,
                'to_cv_range' => $promo->to_cv_range,
                'pwp_value' => $promo->pwp_value,
                'min_purchase_qty' => $promo->min_purchase_qty,
                'active' => $promo->active,
                'product_category_ids' => [
                    'ids' => $productCategoryIds,
                    'data' =>  $productCategoryIdsData
                ],
                'product_ids' => [
                    'ids' => $productIds,
                    'data' =>  $productIdsData
                ],
                'kit_ids' => [
                    'ids' => $kittingIds,
                    'data' => $kittingIdsData
                ],
                'currency' => $currency,
                'conditions' => [
                    'operator' => $promo->options_relation,
                    'options' => $resultArray
                ],
                'general' => $generalTab,
                'deleted_ids' => [
                    "products" => []
                ]
            ];
        }else{
            $data = [
                'promo_id' => '',
                'country_id' => $countryId,
                'name' => '',
                'start_date' => '',
                'end_date' => '',
                'promo_type_id' => '',
                'from_cv_range' => '',
                'to_cv_range' => '',
                'pwp_value' => '',
                'min_purchase_qty' => '',
                'active' => '',
                'product_category_ids' => [
                    'ids' => [],
                    'data' => []
                ],
                'product_ids' => [
                    'ids' => [],
                    'data' =>  []
                ],
                'kit_ids' => [
                    'ids' => [],
                    'data' => []
                ],
                'currency' => $currency,
                'conditions' => [
                    'operator' => '',
                    'options' => []
                ],
                'general' => [],
                'deleted_ids' => [
                    "products" => []
                ]
            ];
        }

        return $data;
    }

    /**
     * create or update promotionFreeItem
     *
     * @param array $data
     * @return array
     */
    public function createOrUpdate(array $data)
    {
        $errorBag = [];

        $promoData = [
            'id' => $data['promo_id'],
            'name' => $data['name'],
            'country_id' => $data['country_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'promo_type_id' => $data['promo_type_id'],
            'from_cv_range' => $data['from_cv_range'],
            'to_cv_range' => $data['to_cv_range'],
            'pwp_value' => $data['pwp_value'],
            'min_purchase_qty' => $data['min_purchase_qty'],
            'options_relation' => $data['conditions']['operator'],
            'active' => 1 //$data['active']
        ];

        //update promoFreeItem if promo_id not null---------------------------------------------------------------------
        if ($data['promo_id'] != null){
            $promo = $this->find($data['promo_id']);

            $promo->update($promoData);
        }
        else //create new promo
        {
            $promo = $this->modelObj->create($promoData);
        }

        //promotion products section------------------------------------------------------------------------------------
        if (!empty($data['product_ids'])) {

            if ($promo->products()->count() > 0) {
                $promo->products()->sync($data['product_ids']['ids']);
            }else{
                $promo->products()->attach($data['product_ids']['ids']);
            }
        }

        //promotion categories section----------------------------------------------------------------------------------
        if (!empty($data['product_category_ids'])) {

            if ($promo->categories()->count() > 0) {
                $promo->categories()->sync($data['product_category_ids']['ids']);
            }else{
                $promo->categories()->attach($data['product_category_ids']['ids']);
            }
        }

        //promotion kitting section-------------------------------------------------------------------------------------
        if (!empty($data['kit_ids'])) {

            if ($promo->kitting()->count() > 0) {
                $promo->kitting()->sync($data['kit_ids']['ids']);
            }else{
                $promo->kitting()->attach($data['kit_ids']['ids']);
            }
        }

        //promotionFreeItem Products Section----------------------------------------------------------------------------
        if (!empty($data['conditions'])) {

            $j = 1;
            $option = 1;
            $optionsArrays = [];
            foreach ($data['conditions']['options'] as $options)
            {
                foreach ($options as $prodOption)
                {
                    $or = false;

                    for ($p = 1; $p < count($prodOption); $p++)
                    {
                        if(!isset($prodOption['set_'.$p])) break;

                        foreach ($prodOption['set_'.$p] as $product)
                        {
                            if ($or){
                                $optionsArrays[$option] .= 'or,';

                                $or = false;
                            }

                            if (array_key_exists($option,$optionsArrays)) {
                                $optionsArrays[$option] .= $product['product_id'].',';
                            }else{
                                $optionsArrays[$option] = $product['product_id'].',';
                            }

                            if ($data['promo_id'] == null){
                                $optionProduct = null;
                            }else{
                                $optionProduct = $this->promoOptionProductsObj
                                    ->where('promo_id', $promo->id)
                                    ->where('option_id', $option)
                                    ->where('product_id', $product['product_id'])
                                    ->first();
                            }

                            $optionProductData = [
                                'promo_id' => $promo->id,
                                'option_id' => $option,
                                'product_id' => $product['product_id'],
                                'quantity' => $product['quantity']
                            ];

                            if ($optionProduct != null){
                                $optionProduct->update($optionProductData);
                            } else{
                                $this->promoOptionProductsObj->create($optionProductData);
                            }
                        }

                        $or = true;
                    }

                    $option++;
                }
            }

            if (!empty($optionsArrays)){
                foreach ($optionsArrays as $key=>$val){

                    $promoOption = $this->promoOptionObj
                        ->where('promo_id', $promo->id)
                        ->where('option_id', $key)
                        ->first();

                    $promoOptionData = [
                        'promo_id' => $promo->id,
                        'option_id' => $key,
                        'option_products' => $val
                    ];

                    if ($promoOption != null){
                        $promoOption->update($promoOptionData);
                    } else{
                        $this->promoOptionObj->create($promoOptionData);
                    }
                }
            }

            $j++;
        }

        //promotion general settings------------------------------------------------------------------------------------
        if (!empty($data['general']))
        {
            foreach ($data['general'] as $key =>  $val)
            {
                $master =  $this->masterObj->where('key', $key)->first();

                $kittingGeneralSettings =  $this->promoGeneralSettingObj
                    ->where('master_id', $master->id)
                    ->where('promo_id',$promo->id)
                    ->pluck('master_data_id')
                    ->toArray();

                $selectedSettings = array_diff($val, $kittingGeneralSettings);

                if (count($selectedSettings)>0) {

                    foreach ($selectedSettings as $setting) {
                        $this->promoGeneralSettingObj->create([
                            'promo_id' => $promo->id,
                            'master_id' => $master->id,
                            'master_data_id' => $setting
                        ]);
                    }

                }

                //remove unselected
                $deSelectedSetting = array_diff($kittingGeneralSettings, $val);

                if (count($deSelectedSetting)>0){

                    foreach ($deSelectedSetting as $delSetting)
                    {
                        $this->promoGeneralSettingObj
                            ->where('promo_id',$promo->id)
                            ->where('master_id', $master->id)
                            ->where( 'master_data_id', $delSetting)
                            ->delete();
                    }

                }
            }
        }

        return array_merge([ 'errors' => $errorBag ] ,
            $this->promotionFreeItemsDetails($data['country_id'], $promo->id));
    }

    public function delete(int $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * retrieve promotion free item by filter member info and ordering products
     *
     * @param array $saleType
     * @param array $promoTypes
     * @param int $countryId
     * @param int $memberType
     * @param string $duration
     * @param array $productFilters
     * @return \Illuminate\Support\Collection
     */
    public function retrievePromotionDetails(
        array $saleType,
        array $promoTypes,
        int $countryId,
        int $memberType,
        string $duration,
        array $productFilters
    )
    {
        $memberTypePromo = $this->promoGeneralSettingObj
            ->distinct('promo_id')
            ->where('master_data_id',$memberType)
            ->pluck('promo_id');

        $promoQueries = $this->modelObj
            ->active()
            ->where('country_id', $countryId)
            ->where('start_date', '<=', $duration)
            ->where('end_date', '>=', $duration)
            ->where('promo_type_id', '>', 0)
            ->whereIn('id', $memberTypePromo)
            ->get();

        $qualifyCampaignCv = 0;

        $promoDatas = [];

        foreach($promoQueries as $promoQuery){

            $promoDetail = $this->promotionFreeItemsDetails($countryId, $promoQuery->id);

            $promoMatch = false;

            if(isset($promoDetail['general']['sale_types'])){

                $promoFromCv = $promoDetail['from_cv_range'];

                $promoToCv = $promoDetail['to_cv_range'];

                $promotionsTypeName = strtolower($promoTypes[$promoDetail['promo_type_id']]);

                ($promotionsTypeName == 'pwp(n)') ? $promotionsTypeName = 'pwp' :  $promotionsTypeName = 'foc';

                if(count($promoDetail['product_category_ids']['ids']) == 0 
                    && count($promoDetail['product_ids']['ids']) == 0 
                    && count($promoDetail['kit_ids']['ids']) == 0){

                    $qualifySumCv = 0;

                    foreach($promoDetail['general']['sale_types'] as $salesTypeId){

                        $salesTypeName = strtolower($saleType[$salesTypeId]);

                        $productCv = collect($productFilters[$promotionsTypeName][$salesTypeName]['product'])->sum('total_cv');

                        $kittingCv = collect($productFilters[$promotionsTypeName][$salesTypeName]['kitting'])->sum('total_cv');

                        $qualifySumCv += $productCv + $kittingCv;

                    }

                    if($promoToCv > 0){
                        if($promoFromCv <= $qualifySumCv && $promoToCv >= $qualifySumCv){
                            $promoMatch = true;

                            $qualifyCampaignCv += $qualifySumCv;
                        }
                    }

                } else {

                    foreach($promoDetail['general']['sale_types'] as $salesTypeId){

                        $salesTypeName = strtolower($saleType[$salesTypeId]);

                        $category = collect($productFilters[$promotionsTypeName][$salesTypeName]['category']);

                        $categoryQualifySumCv = $category
                            ->whereIn('category_id', $promoDetail['product_category_ids']['ids'])
                            ->sum('total_cv');

                        if($promoToCv > 0){
                            if($promoFromCv <= $categoryQualifySumCv && $promoToCv >= $categoryQualifySumCv){
                                $promoMatch = true;

                                $qualifyCampaignCv += $categoryQualifySumCv;

                                break;
                            }
                        }

                        $product = collect($productFilters[$promotionsTypeName][$salesTypeName]['product']);

                        $productQualifySumCv = $product
                            ->whereIn('product_id', $promoDetail['product_ids']['ids'])
                            ->where('qty', '>=', $promoDetail['min_purchase_qty'])
                            ->sum('total_cv');

                        if($promoToCv > 0){
                            if($promoFromCv <= $productQualifySumCv && $promoToCv >= $productQualifySumCv){
                                $promoMatch = true;

                                $qualifyCampaignCv += $productQualifySumCv;

                                break;
                            }
                        }

                        $kitting = collect($productFilters[$promotionsTypeName][$salesTypeName]['kitting']);

                        $kittingQualifySumCv = $kitting
                            ->whereIn('kitting_id', $promoDetail['kit_ids']['ids'])
                            ->where('qty', '>=', $promoDetail['min_purchase_qty'])
                            ->sum('total_cv');

                        if($promoToCv > 0){
                            if($promoFromCv <= $kittingQualifySumCv && $promoToCv >= $kittingQualifySumCv){
                                $promoMatch = true;

                                $qualifyCampaignCv += $kittingQualifySumCv;

                                break;
                            }
                        }
                    }
                }
            }

            if($promoMatch){
                $promoDatas[] = $promoDetail;
            }
        }

        return collect(['qualifyCampaignCv' => $qualifyCampaignCv, 'promoData' => $promoDatas]);
    }
}