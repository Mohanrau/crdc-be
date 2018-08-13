<?php
namespace App\Repositories\Products;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\ResourceRepository,
    Helpers\ValueObjects\ProductKitting,
    Interfaces\Products\ProductCategoryInterface,
    Interfaces\Products\ProductInterface,
    Interfaces\Masters\MasterInterface,
    Models\Languages\Language,
    Models\Locations\Country,
    Models\Locations\Entity,
    Models\Products\Product,
    Models\Products\ProductLocation,
    Models\Products\ProductDescription,
    Models\Products\ProductName,
    Models\Products\ProductPrice,
    Models\Products\ProductImage,
    Models\Products\ProductGeneralSetting,
    Models\Products\ProductCategory,
    Models\Products\ProductRentalPlan,
    Models\Products\ProductRentalCvAllocation,
    Models\Products\ProductActive,
    Models\Currency\Currency,
    Models\Masters\Master,
    Repositories\BaseRepository,
    Services\Sales\CommissionService
};
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductRepository extends BaseRepository implements ProductInterface
{
    use AccessControl;

    private
        $countryObj,
        $categoryObj,
        $languageObj,
        $productNameObj,
        $productLocationObj,
        $productPriceObj,
        $productDescriptionObj,
        $productImageObj,
        $productGeneralSettingObj,
        $productActiveObj,
        $masterObj,
        $masterInterfaceObj,
        $productCategoryObj,
        $productRentalPlanObj,
        $productRentalCvAllocationObj,
        $entityObj,
        $currencyObj,
        $commissionService
    ;

    /**
     * ProductRepository constructor.
     *
     * @param Product $model
     * @param ProductName $productName
     * @param Country $countryModel
     * @param ProductCategoryInterface $productCategoryRepository
     * @param Language $language
     * @param ProductLocation $productLocation
     * @param ProductPrice $productPrice
     * @param ProductGeneralSetting $productGeneralSetting
     * @param ProductDescription $productDescription
     * @param ProductImage $productImage
     * @param ProductCategory $productCategory
     * @param ProductRentalPlan $productRentalPlan
     * @param ProductRentalCvAllocation $productRentalCvAllocation
     * @param ProductActive $productActive
     * @param Currency $currency
     * @param Master $master
     * @param MasterInterface $masterInterface
     * @param Entity $entity
     * @param CommissionService $commissionService
     */
    public function __construct(
        Product $model,
        ProductName $productName,
        Country $countryModel,
        ProductCategoryInterface $productCategoryRepository,
        Language $language,
        ProductLocation $productLocation,
        ProductPrice $productPrice,
        ProductGeneralSetting $productGeneralSetting,
        ProductDescription $productDescription,
        ProductImage $productImage,
        ProductCategory $productCategory,
        ProductRentalPlan $productRentalPlan,
        ProductRentalCvAllocation $productRentalCvAllocation,
        ProductActive $productActive,
        Currency $currency,
        Master $master,
        MasterInterface $masterInterface,
        Entity $entity,
        CommissionService $commissionService
    )
    {
        parent::__construct($model);

        $this->countryObj = $countryModel;

        $this->categoryObj = $productCategoryRepository;

        $this->languageObj = $language;

        $this->productNameObj = $productName;

        $this->productLocationObj = $productLocation;

        $this->productPriceObj = $productPrice;

        $this->productGeneralSettingObj = $productGeneralSetting;

        $this->productDescriptionObj = $productDescription;

        $this->productImageObj = $productImage;

        $this->productCategoryObj = $productCategory;

        $this->productRentalPlanObj = $productRentalPlan;

        $this->productRentalCvAllocationObj = $productRentalCvAllocation;

        $this->productActiveObj = $productActive;

        $this->masterObj = $master;

        $this->masterInterfaceObj = $masterInterface;

        $this->entityObj = $entity;

        $this->currencyObj = $currency;

        $this->commissionService = $commissionService;
    }

    /**
     * import YY Products
     *
     * @param array $data
     * @return array
     */
    public function importYYProducts(array $data)
    {
        try {
            //save the request body-----------------------------------------------------------------------------------------
            if (!file_exists('yy'))
            {
                mkdir('yy');
            }

            $fileName  = date('Y-m-d_H_i_s').".txt";

            $flatFile = fopen("yy/".$fileName, "w");

            fwrite($flatFile, json_encode($data));

            $errorBag = [];

            //process product categories object ------------------------------------------------------------------------
            if (isset($data['product_category']))
            {
                $categories = $data['product_category'];

                $minLevel = collect($categories)->min('level');

                $maxLevel = collect($categories)->max('level');

                for($level = $minLevel; $level <= $maxLevel; $level++){

                    $dataLevels = collect($categories)->where('level', $level);

                    $dataLevels
                        ->each(function ($productCategory) use ($level){

                            $category = $this->productCategoryObj
                                ->where('yy_category_id', $productCategory['id'])
                                ->first();

                            $parentCategory = $this->productCategoryObj
                                ->where('yy_category_id', $productCategory['parent_id'])
                                ->first();

                            $categoryData = [
                                'yy_category_id' => $productCategory['id'],
                                'parent_id' => is_null($parentCategory)? 0: $parentCategory->id,
                                'name' => $productCategory['name'],
                                'code' => $productCategory['code']
                            ];

                            if ($category === null) {
                                $this->productCategoryObj->create($categoryData);
                            } else {
                                $category->update($categoryData);
                            }
                        });
                }
            }

            //process products list ----------------------------------------------------------------------------------------
            if (isset($data['products']))
            {
                foreach ($data['products'] as $product)
                {
                    $entitiesData = [];

                    $productObj = $this->modelObj
                        ->where('sku', $product['sku'])
                        ->first();

                    $categoryId = $this->productCategoryObj
                        ->where('yy_category_id', $product['categoryid'])
                        ->first()->id;

                    $productData =  [
                        'yy_product_id' => $product['id'],
                        'category_id' =>  $categoryId,
                        'name' => $product['name'],
                        'sku' => $product['sku'],
                        'uom' => $product['UOM'],
                        'inventorize' => isset($product['inventorize']) ? $product['inventorize'] : 0,
                        'is_dummy_code' => (strtolower($product['isdummycode']) == 'y') ? 1 : 0
                    ];

                    //create the product--------------------------------------------------------------------------------
                    if ($productObj === null) {
                        $productObj = $this->modelObj->create($productData);
                    } else { //update the product
                        $productObj->update($productData);
                    }

                    if (count($product['entities']) > 0){
                        //handle entities section-----------------------------------------------------------------------
                        foreach ($product['entities'] as $entity){
                            $entityItem = $this->entityObj
                                ->where('name' , $entity['entity_code'])
                                ->first();

                            //create entities if not exists
                            if ($entityItem === null) continue;

                            $entitiesData[] = $entityItem->id;

                            //sync the products with entities-----
                            $productObj->entity()->syncWithoutDetaching([$entityItem->id]);

                            //get product name if exists---------------------------------------------
                            $productName = null;

                            $productName = $this->productNameObj
                                ->where('product_id', $productObj->id)
                                ->where('country_id', $entityItem->country_id)
                                ->where('entity_id', $entityItem->id)
                                ->first();

                            if (is_null($productName)){
                                $productObj->productNames()->create([
                                    'country_id' => $entityItem->country_id,
                                    'entity_id' => $entityItem->id,
                                    'name' => $entity['name']
                                ]);
                            } else{
                                $productName->update([
                                    'name' => $entity['name']
                                ]);
                            }
                        }

                        // set the ibs active status--------------------------------------------------------------------
                        foreach ($entitiesData as $entity)
                        {
                            $entity = $this->entityObj->find($entity);

                            $country = $entity->country()->first();

                            $productActive =  $this->productActiveObj
                                ->where('product_id' , $productObj->id)
                                ->where('country_id' , $country->id)
                                ->first();

                            $yyActive =  collect($product['entities'])
                                ->where('entity_code', $entity->name)
                                ->pluck('yy_active')
                                ->toArray();


                            $yyActive =  collect($product['entities'])
                                ->where('entity_code', $entity->name)
                                ->pluck('yy_active')
                                ->toArray();

                            if (is_null($productActive)){
                                $this->productActiveObj->create([
                                    'country_id' =>  $country->id,
                                    'product_id' => $productObj->id,
                                    'ibs_active' => 0,
                                    'yy_active' => ($yyActive[0] == "true")? 1 : 0
                                ]);
                            }else{
                                $productActive->update([
                                    'yy_active' => ($yyActive[0] == "true")? 1 : 0
                                ]);
                            }
                        }
                    }

                    //check if the sizegroup exists---------------------------------------------------------------------
                    if (isset($product['sizegroup'])){
                        $master = $this->masterObj
                            ->where('key',  trim(str_replace(' ','_',$product['sizegroup'])))
                            ->first();

                        if (is_null($master)){

                            $master = $this->masterObj->create([
                                'title' => $product['sizegroup'],
                                'key' => strtolower(
                                    trim(str_replace(' ','_',$product['sizegroup']))
                                )
                            ]);

                            collect($product['size'])->each(function ($value) use ($master){
                                $master->masterData()->create([
                                    'title' => $value
                                ]);
                            });
                        }

                        $productObj->sizeGroups()->sync([$master->id]);
                    }
                }
            }

            //process product Prices list-------------------------------------------------------------------------------
            if (isset($data['pricelist'])){
                foreach ($data['pricelist'] as $price)
                {
                    $priceObj = $this->productPriceObj
                        ->where('yy_id', $price['id'])
                        ->where('yy_id', '<>', '')
                        ->first();

                    $country = $this->countryObj
                        ->where('code', $price['countrycode'])
                        ->orWhere('code_iso_2', $price['countrycode'])
                        ->first();

                    if ($country === null){
                        $errorBag[$price['productid']][] = __('message.product.country-not-exists', [
                            'code' => $price['countrycode'],
                            'productID' => $price['productid']
                        ]);

                        continue;
                    }

                    $entity = $country->entity()->first();

                    $product = $this->modelObj
                        ->where('yy_product_id', $price['productid'])
                        ->first();

                    $currency = $this->currencyObj
                        ->where('code', $price['currency_code'])
                        ->first();

                    if ($currency === null){
                        $currency = $this->currencyObj->create([
                            'name' => $country->name .' '. $price['currency_code'],
                            'code' => $price['currency_code'],
                            'active' => 1
                        ]);
                    }

                    $productPriceData = [
                        'yy_id' => $price['id'],
                        'country_id' => $country->id,
                        'entity_id' => $entity->id,
                        'product_id' => $product->id,
                        'currency_id' => $currency->id,

                        'rp_price' => $price['nett_price_rp'],
                        'nmp_price' => $price['nett_price_nmp'],

                        'effective_date' => Carbon::parse($price['effective_date']),
                        'expiry_date' => Carbon::parse($price['expiry_date']),

                        'active' => 1,
                    ];

                    if ($priceObj === null) {
                        $this->productPriceObj->create($productPriceData);
                    } else { //update the product
                        $priceObj->update($productPriceData);
                    }
                }
            }

            return
                (count($errorBag) > 0)? ['errors' => $errorBag] :
                    ['status' => trans('message.product.import-success')];

        }catch (\Exception $exception) {

            return [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile()
            ];
        }
    }

    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param int $categoryId
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getProductsByFilters(
        int $countryId,
        int $categoryId = 0,
        int $active = 0,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $entity = $this->countryObj->find($countryId)->entity()->first();

        $data = $entity
            ->products()
            ->distinct('id')
            ->orderBy($orderBy, $orderMethod);

        //check if categoryId is applied
        if ($categoryId > 0) {
            $data = $data
                ->where('category_id', $categoryId);
        }

        //check if the product is active
        if ($active >= 0) {
            $data = $data
                ->join('product_active_countries', function ($join) use ($countryId, $active){
                    $join->on('products.id','=','product_active_countries.product_id')
                        ->where('product_active_countries.ibs_active', $active)
                        ->where('product_active_countries.country_id', $countryId);
                });
        }

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        //get the total records
        $totalRecords = collect(
            [
                'total' => $data->get()->count()
            ]
        );

        $data = ($paginate) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();

        //attach ibs active
        collect($data)->each(function ($product) use ($countryId){
            $productName = $product->getProductName($countryId);

            $product->name = ($productName) ? $productName->name : $product->name;

            $productActive = $product
                ->productActiveByCountry($countryId)
                ->first();

            $product->ibs_active = $productActive->ibs_active;

            $product->yy_active = $productActive->yy_active;
        });

        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * search in product by product_name or product_sku(code)
     *
     * @param int $countryId
     * @param int $categoryId
     * @param string $text
     * @param int $locationId
     * @param int $active
     * @param bool $checkDates
     * @param array $salesTypes
     * @param array|null $includeCategories
     * @param array|null $includeProducts
     * @param array|null $excludeProducts
     * @param bool $exactSearch
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return \Illuminate\Support\Collection|mixed
     */
    public function searchProducts(
        int $countryId,
        int $categoryId = 0,
        string $text,
        int $locationId = 0,
        int $active = 0,
        bool $checkDates = false,
        array $salesTypes = [],
        array $includeCategories = null,
        array $includeProducts = null,
        array $excludeProducts = null,
        bool $exactSearch = false,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $entity = $this->countryObj->find($countryId)->entity()->first();

        $data = $entity->products();

        //check if categoryId is applied
        if ($categoryId > 0) {
            $data = $data
                ->where('category_id', $categoryId);
        }

        //check if salesType id's matched the given sales types
        if (!empty($salesTypes)){
            $data = $data
                ->distinct('products.id')
                ->join('product_general_settings', function ($join) use ($salesTypes, $countryId, $entity){
                    $join->on('products.id','=','product_general_settings.product_id')
                        ->whereIn('product_general_settings.master_data_id',$salesTypes)
                        ->where('product_general_settings.country_id',$countryId)
                        ->where('product_general_settings.entity_id',$entity->id)
                    ;
                });
        }

        //check product location
        if ($locationId > 0) {
            $data = $data
                ->join('product_locations', function ($join) use ($locationId, $countryId){
                    $join->on('products.id', '=', 'product_locations.product_id')
                        ->where('product_locations.location_id', $locationId)
                        ->where('product_locations.country_id', $countryId);
                });
        }

        //check if the product is active
        if ($active >= 0) {
            $data = $data
                ->join('product_active_countries', function ($join) use ($countryId, $active){
                    $join->on('products.id','=','product_active_countries.product_id')
                        ->where('product_active_countries.ibs_active', $active)
                        ->where('product_active_countries.country_id', $countryId);
                });
        }

        //check for product categories
        if (isset($includeCategories) && count($includeCategories) > 0) {
            $data = $data
                ->whereIn('products.category_id', $includeCategories);
        }

        //check for include product
        if (isset($includeProducts)) {
            if(count($includeProducts) > 0) {
                $data = $data
                    ->whereIn('products.id', $includeProducts);
            }
            else if (isset($includeCategories) && count($includeCategories) <= 0) {
                $data = $data
                    ->where('products.id', -1); //make no row return
            }
        }

        //check for exclude product
        if (isset($excludeProducts) && count($excludeProducts) > 0) {
            $data = $data
                ->whereNotIn('products.id', $excludeProducts);
        }

        //todo check the effective dates for the product for sales
        //check effective dates for the product

        $data =  $data
            ->where(function ($query) use ($text, $exactSearch) {
                if ($exactSearch){
                    $query
                        ->where('products.sku', $text)
                        ->orWhere('products.name', $text);
                }else{
                    $query
                        ->where('products.sku', 'like', '%' . $text . '%')
                        ->orWhere('products.name', 'like', '%' . $text . '%');
                }
            })
            ->select('products.id', 'products.name', 'products.sku', 'products.category_id');

        $totalRecords = collect(
            [
                'total' => $data->get()->count()
            ]
        );

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        //attach ibs active
        collect($data)->each(function ($product) use ($countryId){
            $productName = $product->getProductName($countryId);

            $product->name = ($productName) ? $productName->name : $product->name;

            $product->ibs_active =
                $product
                    ->productActiveByCountry($countryId)
                    ->first()
                    ->ibs_active;
        });

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get one user by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get one product by productId id and countryId
     *
     * Cv breakdown is only provided if the locationId is set
     *
     * @param int $countryId
     * @param int $productId
     * @param int|null $locationId
     * @return array
     * @throws \Exception
     */
    public function productDetails(int $countryId, int $productId, $locationId = null)
    {
        $product = $this->modelObj->with(
            [
                'productGeneralSetting' =>
                    function ($query) use ($countryId)
                    {
                        $query->where('country_id', $countryId);
                    }
            ]
        )->findOrFail($productId);

        $country = $this->countryObj->find($countryId);

        //load the granted location give for the user if he back_office
        $locations = $this->getLocationsByCountry($country, $countryId);

        //get entity id
        $entityId = $product->productAvailableInCountry($countryId)->first()->id;

        //get product categories
        $categories = $this->categoryObj->find($product->category_id, [
            'parent.parent.parent.parent'
        ]);

        //get product ibs active
        $productActive = $product->productActiveByCountry($countryId)->first();

        //get latest product price
        $productPrices = $product->getProductPriceByCountry($countryId);

        //get product promo prices for a given country
        $productPricePromos = $product->getProductPromoPriceByCountry($countryId);

        //get default tax for a country
        $countryTax = $country->taxes()->default()->active()->first();

        //get product general section
        $generalSetting = $this->productGeneralSettingObj
            ->getMasters($countryId, $entityId, $productId);

        $generalTab = [];

        if (count($generalSetting)>0){
            foreach ($generalSetting as $master){
                $generalTab[$master->key] = $this->productGeneralSettingObj
                    ->where('master_id', $master->masterId)
                    ->where('product_id', $product->id)
                    ->where('country_id', $country->id)
                    ->pluck('master_data_id')
                    ->toArray();
            }
        }

        //get dummy details for the given productObj
        $dummyCode = $product
            ->dummy()
            ->with('dummyProducts')
            ->where('country_id', $countryId)
            ->active()
            ->first();

        //get tax
        $tax = [];
        if ($countryTax != null){
            $tax = array_merge(
                ($countryTax != null)? $countryTax->toArray() : [],
                ['tax_desc' => $country->tax_desc]
            );
        }

        //get product images
        $images = $product->productImages($countryId)->get();

        $selectedImages = $product->productImages()->default()->active()->get();

        //get languages
        $languages = $this->languageObj->active()->get();

        // product attributes
        $attributeCategory = optional($product->sizeGroups())->first();

        //product name
        $productName = $product->getProductName($countryId);

        $data = [
            'product_id' => $product->id,
            'name' => ($productName) ? $productName->name : $product->name,
            'sku' => $product->sku,
            'entity_id' => $entityId,
            'country_id' => $countryId,
            'categories' => $categories,
            'languages' => $languages,
            'ibs_active' => $productActive->ibs_active,
            'yy_active' => optional($productActive)->yy_active,
            'uom' => optional($product)->uom,
            'currency' => optional($productPrices)->currency,
            'base_price' => $productPrices,
            'promotion_prices' => $productPricePromos,
            'location' => [
                'selected' => $product->productLocations($countryId)->pluck('location_id'),
                'list' => $locations
            ],
            'description' => $product->productDescriptions,
            'images' => [
                'list' => $images,
                'selected' => $selectedImages
            ],
            'general' => $generalTab,
            'dummy' => $dummyCode,
            'rental_plan' => $product->productRentalPlan,
            'virtual_product' => $product->virtualProducts,
            'unit_cv' => (object) []
        ];

        if ($locationId && $locations->where('id', $locationId)->count() !== 0) {
            if ($price = $this->productEffectivePricing(
                $countryId,
                $product->id,
                [$locationId]
            )) {
                $data = array_merge(
                    $data,
                    [
                        'unit_cv' => $this->commissionService->calculateBreakdown(
                            new ProductKitting(
                                $product,
                                $price
                            )
                        )->toArray()
                    ]
                );
            }
        }

        $data = array_merge($data,['size_groups' => $attributeCategory]);

        return ($countryTax != null) ? array_merge($data, ['tax' => $tax]) : $data;
    }

    /**
     * get the effective price for a given product id based on locations and startDate
     *
     * @param int $countryId
     * @param int $productId
     * @param array $locationsIds
     * @param null $startDate
     * @return array|mixed
     */
    public function productEffectivePricing(
        int $countryId,
        int $productId,
        array $locationsIds = [],
        $startDate = null)
    {
        $product = $this->find($productId);

        //check the price if locationIds is set
        $promoPrice =  $product->getEffectivePromoProductPrice($countryId, $locationsIds, $startDate);

        if ($promoPrice == null){
            $basePrice =  $product->getEffectiveBaseProductPrice($countryId, $locationsIds, $startDate);

            return $basePrice;
        }

        return $promoPrice;
    }

    /**
     * save the changes on product details api
     *
     * @param array $data
     * @param int $id
     * @return array|mixed
     * @throws \Exception
     */
    public function update(array $data, int $id)
    {
        $errorBag = [];

        $product = $this->modelObj->find($data['product_id']);

        //save product price - base price-------------------------------------------------------------------------------
        $productPrices = $this->productPriceObj->findOrFail($data['base_price']['id']);

        $productBasePrice = $data['base_price'];

        $productPrices->update([
            'gmp_price_gst' => $productBasePrice['gmp_price_tax'],
            'rp_price_gst' => $productBasePrice['rp_price_tax'],
            'effective_date' => $productBasePrice['effective_date'],
            'expiry_date' => $productBasePrice['expiry_date'],

            'base_cv' => $productBasePrice['base_cv'],
            'wp_cv' => $productBasePrice['wp_cv'],
            'cv1' => $productBasePrice['cv_1'],
            'cv2' => $productBasePrice['cv_2'],
            'cv3' => $productBasePrice['cv_3'],
            'cv4' => $productBasePrice['cv_4'],
            'cv5' => $productBasePrice['cv_5'],
            'cv6' => $productBasePrice['cv_6'],

            'welcome_bonus_l1' => $productBasePrice['bonuses']['welcome_bonus_1'],
            'welcome_bonus_l2' => $productBasePrice['bonuses']['welcome_bonus_2'],
            'welcome_bonus_l3' => $productBasePrice['bonuses']['welcome_bonus_3'],
            'welcome_bonus_l4' => $productBasePrice['bonuses']['welcome_bonus_4'],
            'welcome_bonus_l5' => $productBasePrice['bonuses']['welcome_bonus_5'],
        ]);

        //virtual product-----------------------------------------------------------------------------------------------
        if(!empty($data['virtual_product']))
        {
            $product->virtualProducts()->detach($data['virtual_product']['virtual_product_id']);

            $product->virtualProducts()->attach($data['virtual_product']['virtual_product_id'], [
                'country_id' => $data['virtual_product']['country_id'],
                'master_data_id' => $data['virtual_product']['master_data_id']
            ]);
        }

        //product locations---------------------------------------------------------------------------------------------
        if (!empty($data['location'])) {

            $productLocations = $product
                ->productLocations($data['country_id'])
                ->pluck('location_id')
                ->toArray();

            $selectedLocation = array_diff($data['location']['selected'], $productLocations);

            if (count($selectedLocation)>0) {
                foreach ($selectedLocation as $location) {
                    $this->productLocationObj->create([
                        'country_id' => $data['country_id'],
                        'entity_id' => $data['entity_id'],
                        'product_id' => $data['product_id'],
                        'location_id' => $location
                    ]);
                }
            }

            //remove unselected
            $deSelectedLocation = array_diff(
                $productLocations,
                $data['location']['selected']
            );

            if (count($deSelectedLocation)>0){
                foreach ($deSelectedLocation as $delLocation)
                {
                    $this->productLocationObj
                        ->where('country_id', $data['country_id'])
                        ->where('entity_id', $data['entity_id'])
                        ->where('product_id', $data['product_id'])
                        ->where( 'location_id', $delLocation)
                        ->delete();
                }
            }
        }

        //save product promos-------------------------------------------------------------------------------------------
        if (!empty($data['promotion_prices'])) {

            foreach ($data['promotion_prices'] as $promo) {

                if ($product->checkProductPromoDateRange(
                    $data['country_id'],
                    (isset($promo['id'])) ? $promo['id'] : 0,
                    $promo['effective_date'])
                ) {
                    $errorBag[] = __('message.product.promo-dates', [
                        'effective_date' => $promo['effective_date'],
                        'expire_date' => $promo['expiry_date']
                    ]);

                    continue;
                };

                $productPromo = [
                    'country_id' => $data['country_id'],
                    'entity_id' => $data['entity_id'],
                    'product_id' => $data['product_id'],
                    'currency_id' => $data['currency']['id'],

                    'gmp_price_gst' => $promo['gmp_price_tax'],
                    'rp_price_gst' => $promo['rp_price_tax'],
                    'rp_price' => $promo['rp_price'],
                    'nmp_price' => $promo['nmp_price'],

                    'effective_date' => $promo['effective_date'],
                    'expiry_date' => $promo['expiry_date'],

                    'base_cv' => $promo['base_cv'],
                    'wp_cv' => $promo['wp_cv'],
                    'cv1' => $promo['cv_1'],
                    'cv2' => $promo['cv_2'],
                    'cv3' => $promo['cv_3'],
                    'cv4' => $promo['cv_4'],
                    'cv5' => $promo['cv_5'],
                    'cv6' => $promo['cv_6'],

                    'welcome_bonus_l1' => $promo['bonuses']['welcome_bonus_1'],
                    'welcome_bonus_l2' => $promo['bonuses']['welcome_bonus_2'],
                    'welcome_bonus_l3' => $promo['bonuses']['welcome_bonus_3'],
                    'welcome_bonus_l4' => $promo['bonuses']['welcome_bonus_4'],
                    'welcome_bonus_l5' => $promo['bonuses']['welcome_bonus_5'],
                    'active' => 1,
                    'promo' => 1
                ];

                //todo check if these locations is subset of the select locations for that product
                if (isset($promo['id'])) {
                    $productPrice = $this->productPriceObj->findOrFail($promo['id']);

                    $productPrice->update($productPromo);

                    if (isset($promo['location_ids'])) {
                        $productPrice->productPromoLocations()->sync($promo['location_ids']);
                    }

                } else {
                    $productPrice = $this->productPriceObj->create($productPromo);

                    if (isset($promo['location_ids'])) {
                        $productPrice->productPromoLocations()->attach($promo['location_ids']);
                    }
                }
            }

        }

        //product description-------------------------------------------------------------------------------------------
        if (!empty($data['description'])) {

            foreach ($data['description'] as $item) {

                if (isset($item['id'])) {
                    $this->productDescriptionObj
                        ->findOrFail($item['id'])
                        ->update($item);
                } else {
                    $this->productDescriptionObj->create(array_merge($item, [
                        'product_id' => $data['product_id']
                    ]));
                }
            }
        }

        //general settings----------------------------------------------------------------------------------------------
        if (!empty($data['general']))
        {
            foreach ($data['general'] as $key =>  $val)
            {
                $master =  $this->masterObj->where('key', $key)->first();

                $productGeneralSettings =  $this->productGeneralSettingObj
                    ->where('country_id', $data['country_id'])
                    ->where('entity_id', $data['entity_id'])
                    ->where('master_id', $master->id)
                    ->where('product_id', $product->id)
                    ->pluck('master_data_id')
                    ->toArray();

                $selectedSettings = array_diff($val, $productGeneralSettings);

                if (count($selectedSettings)>0) {

                    foreach ($selectedSettings as $setting) {
                        $this->productGeneralSettingObj->create([
                            'country_id' => $data['country_id'],
                            'product_id' => $data['product_id'],
                            'entity_id' => $data['entity_id'],
                            'master_id' => $master->id,
                            'master_data_id' => $setting
                        ]);
                    }
                }

                //remove unselected
                $deSelectedSetting = array_diff($productGeneralSettings, $val);

                if (count($deSelectedSetting)>0){

                    foreach ($deSelectedSetting as $delSetting)
                    {
                        $this->productGeneralSettingObj
                            ->where('country_id', $data['country_id'])
                            ->where('entity_id', $data['entity_id'])
                            ->where('product_id',$data['product_id'])
                            ->where('master_id', $master->id)
                            ->where( 'master_data_id', $delSetting)
                            ->delete();
                    }

                }
            }
        }

        //product images------------------------------------------------------------------------------------------------
        if (!empty($data['images'])) {

            $oldFileArray = $this->productImageObj
                ->where('product_id', $data['product_id'])
                ->pluck('image_path')
                ->toArray();

            $newFileArray = [];

            foreach ($data['images']['list'] as $image) {

                if (isset($image['id'])) {
                    $this->productImageObj
                        ->findOrFail($image['id'])
                        ->update($image);
                } else {
                    $this->productImageObj->create(array_merge($image, [
                        'country_id' => $data['country_id'],
                        'entity_id' => $data['entity_id'],
                        'product_id' => $data['product_id'],
                    ]));
                }

                array_push($newFileArray, $image['image_path']);
            }

            Uploader::synchronizeServerFile(Uploader::getUploaderSetting(true)['product_standard_image'], $oldFileArray, $newFileArray, false);
        }

        //insert or update product rental plan---------------------------------------------------------------------------
        if (!empty($data['rental_plan']))
        {
            collect($data['rental_plan'])->each(function($plan)
            use ($data){
                $productRentalData = [
                    'country_id' => $data['country_id'],
                    'entity_id' => $data['entity_id'],
                    'product_id' => $data['product_id'],
                    'initial_payment' => $plan['initial_payment'],
                    'monthly_repayment' => $plan['monthly_repayment'],
                    'total_payment' => $plan['total_payment'],
                    'tenure' => $plan['tenure'],
                    'number_of_cw' => intval($plan['tenure']) * 2,
                ];

                //get product rental record
                $productRentalDetail = $this->productRentalPlanObj
                    ->where('country_id', $data['country_id'])
                    ->where('entity_id', $data['entity_id'])
                    ->where('product_id', $data['product_id'])
                    ->where('tenure', $plan['tenure'])
                    ->first();

                if($productRentalDetail){

                    $productRentalData['updated_by'] = Auth::id();

                    $productRentalDetail->update($productRentalData);

                } else {

                    $productRentalDetail = Auth::user()->createdBy($this->productRentalPlanObj)
                        ->create($productRentalData);
                }

                $productRentalId = $productRentalDetail->id;

                //Remove product rental cv allocation record
                $this->productRentalCvAllocationObj
                    ->where('product_rental_plan_id', $productRentalId)
                    ->delete();

                collect($plan['product_rental_cv_allocation'])->each(function($cvAllocation)
                use ($productRentalId){
                    $this->productRentalCvAllocationObj->create([
                        'product_rental_plan_id' => $productRentalId,
                        'cw_number' => $cvAllocation['cw_number'],
                        'allocate_cv' => $cvAllocation['allocate_cv']
                    ]);
                });
            });
        }

        //product Active Status-----------------------------------------------------------------------------------------
        $productActive = $product->productActiveByCountry($data['country_id']);

        $productActive->update(
            [
                'ibs_active' => $data['ibs_active']
            ]
        );

        return  (count($errorBag) >0 ) ?
            [ 'errors' => $errorBag ] :
            $this->productDetails($data['country_id'], $data['product_id']);
    }

    /**
     * delete product image for a given image id
     *
     * @param int $id
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     */
    public function deleteProductImage(int $id)
    {
        $productImage = $this->productImageObj->findOrFail($id);

        Uploader::deleteServerFile(Uploader::getUploaderSetting(true)['product_standard_image'], [$productImage['image_path']]);

        return ['status' => ($productImage->delete()) ?
            trans('message.delete.success') :trans('message.delete.fail')];

    }

}