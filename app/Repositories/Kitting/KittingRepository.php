<?php
namespace App\Repositories\Kitting;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\ResourceRepository,
    Interfaces\Kitting\KittingInterface,
    Interfaces\Products\ProductInterface,
    Models\Locations\Country,
    Models\Languages\Language,
    Models\Masters\Master,
    Repositories\BaseRepository,
    Helpers\ValueObjects\ProductKitting,
    Services\Sales\CommissionService
};
use App\Models\Kitting\{
    Kitting,
    KittingGeneralSetting,
    KittingPrice,
    KittingProduct,
    KittingDescription,
    KittingImage
};
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\{
    Database\Eloquent\Model,
    Support\Facades\DB
};
use phpDocumentor\Reflection\DocBlock\Description;

class KittingRepository extends BaseRepository implements KittingInterface
{
    use AccessControl;

    private
        $countryObj,
        $languageObj,
        $kittingGeneralSettingObj,
        $kittingPriceObj,
        $kittingProductObj,
        $productObj,
        $KittingDescriptionObj,
        $kittingImagesObj,
        $masterObj,
        $commissionService
    ;

    /**
     * KittingRepository constructor.
     *
     * @param Kitting $model
     * @param Country $country
     * @param Language $language
     * @param KittingGeneralSetting $kittingGeneralSetting
     * @param KittingPrice $kittingPrice
     * @param KittingProduct $kittingProduct
     * @param KittingDescription $kittingDescription
     * @param KittingImage $kittingImage
     * @param Master $master
     * @param ProductInterface $productRepository
     * @param CommissionService $commissionService
     */
    public function __construct(
        Kitting $model,
        Country $country,
        Language $language,
        KittingGeneralSetting $kittingGeneralSetting,
        KittingPrice $kittingPrice,
        KittingProduct $kittingProduct,
        KittingDescription $kittingDescription,
        KittingImage $kittingImage,
        Master $master,
        ProductInterface $productRepository,
        CommissionService $commissionService
    )
    {
        parent::__construct($model);

        $this->countryObj = $country;

        $this->languageObj = $language;

        $this->kittingGeneralSettingObj = $kittingGeneralSetting;

        $this->kittingPriceObj = $kittingPrice;

        $this->kittingProductObj = $kittingProduct;

        $this->productObj = $productRepository;

        $this->KittingDescriptionObj = $kittingDescription;

        $this->kittingImagesObj = $kittingImage;

        $this->masterObj = $master;

        $this->commissionService = $commissionService;
    }

    /**
     * get one record
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get kitting by filters
     *
     * @param int $countryId
     * @param bool|null $isEsac
     * @param string $kittingCode
     * @param string $productSku
     * @param array $salesType
     * @param int $locationId
     * @param array|null $includeCategories
     * @param array|null $includeKittings
     * @param array|null $excludeKittings
     * @param string $text
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getKittingByFilters(
        int $countryId,
        bool $isEsac = null,
        string $kittingCode = '',
        string $productSku = '',
        array $salesType = [],
        int $locationId = 0,
        array $includeCategories = null,
        array $includeKittings = null,
        array $excludeKittings = null,
        string $text = '',
        int $active = 1,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = DB::table('kitting')
            ->leftJoin('kitting_descriptions', 'kitting_descriptions.kitting_id', '=', 'kitting.id')
            ->select(
                'kitting.*',
                'kitting_descriptions.marketing_description'
            )
            ->where('kitting.country_id', $countryId);

        if ($kittingCode != '') {
            $data->where('kitting.code', 'like', '%' . $kittingCode . '%');
        }

        //check is esac----------------
        if ( !is_null($isEsac) ) {
            $data->where('kitting.is_esac', $isEsac);
        }

        //check kitting active status
        if ($active >= 0) {
            $data->where('kitting.active', $active);
        }

        if ($productSku != '') {
            $data
                ->join('kitting_products', 'kitting.id', '=', 'kitting_products.kitting_id')
                ->join('products', function ($query) use ($productSku){
                    $query->on('kitting_products.product_id', '=', 'products.id')
                        ->where('products.sku', 'like', '%' . $productSku . '%');
                });
        }

        //check the sales type
        if (!empty($salesType)) {
            $data
                ->join('kitting_general_settings', function ($join) use ($salesType) {
                    $join->on('kitting.id', '=', 'kitting_general_settings.kitting_id')
                        ->whereIn('kitting_general_settings.master_data_id', $salesType);
                });
        }

        //check product location
        if ($locationId > 0) {
            $data
                ->join('kitting_locations', function ($join) use ($locationId){
                    $join->on('kitting.id', '=', 'kitting_locations.kitting_id')
                        ->where('kitting_locations.location_id', $locationId);
                });
        }

        //check for product categories
        if (isset($includeCategories) && count($includeCategories) > 0) {
            $productIds = $this
                ->countryObj
                ->find($countryId)
                ->entity()
                ->first()
                ->products()
                ->whereIn('category_id', $includeCategories)
                ->pluck('id')
                ->toArray();

            $kittingsIds = $this
                ->kittingProductObj
                ->whereIn('product_id', $productIds)
                ->pluck('kitting_id')
                ->toArray();

            $data = $data
                ->whereIn('kitting.id', $kittingsIds);
        }

        //check for include kitting
        if (isset($includeKittings)) {
            if (count($includeKittings) > 0) {
                $data = $data
                    ->whereIn('kitting.id', $includeKittings);
            }
            else if (isset($includeCategories) && count($includeCategories) <= 0) {
                $data = $data
                    ->where('kitting.id', -1); //make no row return
            }
        }

        //check for exclude kitting-------------------------------
        if (isset($excludeKittings) && count($excludeKittings) > 0) {
            $data = $data
                ->whereNotIn('kitting.id', $excludeKittings);
        }

        //search for the text if it is not empty-----------------
        if ($text !== '') {
            $data = $data
                ->where(function ($query) use ($text) {
                    $query
                        ->where('kitting.code', 'like', '%' . $text . '%')
                        ->orWhere('kitting.name', 'like', '%' . $text . '%');
                });
        }

        $data
            ->groupBy('kitting.name')
            ->orderBy("kitting.".$orderBy, $orderMethod);

        $totalRecords = collect(
            [
                'total' => $data->get()->count()
            ]
        );

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get kitting details for a given countryId and optional kittingId
     *
     * @param int $countryId
     * @param int $kittingId
     * @return array|string
     * @throws \Exception
     */
    public function kittingDetails(int $countryId, int $kittingId = null)
    {
        //country info---------------------------------------
        $country = $this->countryObj->find($countryId);

        //currency info
        $currency = $country->currency()->first();

        //locations info
        //load the granted location give for the user if he back_office
        $locations = $this->getLocationsByCountry($country, $countryId);

        //get default tax for a country
        $countryTax = $country->taxes()->default()->active()->first();

        //get tax
        $tax = [];
        if ($countryTax != null){
            $tax = array_merge(
                ($countryTax != null)? $countryTax->toArray() : [],
                ['tax_desc' => $country->tax_desc]
            );
        }

        if ($kittingId > 0){

            $kitting = $this->modelObj
                ->with('kittingGeneralSetting')
                ->findOrFail($kittingId);

            //kitting price-------------------------------------------------------------------
            $kittingPrice = $kitting->kittingPrice()->first();

            //kitting products----------------------------------------------------------------
            $kittingProducts = $kitting->kittingProducts($countryId, [
                'product.category.parent.parent.parent.parent'
            ])->get();

            //attach effective price for the given product
            collect($kittingProducts)->each(function ($product) use ($countryId){
               $product->product->product_prices_latest = $product->product->getEffectiveBaseProductPrice($countryId);

                $productName = $product->product->getProductName($countryId);

                $product->product->name = ($productName) ? $productName->name : $product->product->name;
            });

            //attach dummies to the product
            collect($kittingProducts)->each(function ($product) use($countryId){
                $product->dummy = $product
                    ->product
                    ->dummy()
                    ->with('dummyProducts')
                    ->where('country_id', $countryId)
                    ->active()
                    ->first();
            });

            //kitting general settings--------------------------------------------------------
            $kittingGeneralSetting = $this->kittingGeneralSettingObj->getMasters($kitting->id);

            $generalTab = [];

            if (count($kittingGeneralSetting)>0){
                foreach ($kittingGeneralSetting as $master){
                    $generalTab[$master->key] = $this->kittingGeneralSettingObj
                        ->where('master_id', $master->masterId)
                        ->where('kitting_id', $kitting->id)
                        ->pluck('master_data_id')
                        ->toArray();
                }
            }

            //kitting images ----------------------------------------------------------------
            $images = $kitting->kittingImages()->get();

            $selectedImages = $kitting
                ->kittingImages()
                ->default()
                ->active()
                ->get();

            $data = [
                'kitting_id' => $kitting->id,
                'country_id' => $countryId,
                'name' => $kitting->name,
                'code' => $kitting->code,
                'is_esac' => $kitting->is_esac,
                'active' => $kitting->active,
                'currency' => $currency,
                'tax' => $tax,
                'kitting_price' => $kittingPrice,
                'kitting_products' => $kittingProducts,
                'kitting_products_total_foc' => $kittingProducts->sum('foc_qty'),
                'kitting_products_total_quantity' => $kittingProducts->sum('quantity'),
                'location' => [
                    'selected' => $kitting->kittingLocations()->pluck('location_id'),
                    'list' => $locations
                ],
                'description' => $kitting->kittingDescriptions,
                'images' => [
                    'list' => $images,
                    'selected' => $selectedImages
                ],
                'general' => $generalTab,
                'deleted_ids' => [
                    "descriptions" => [],
                    "products" => []
                ],
                'unit_cv' => ($kittingPrice)
                    ? $this->commissionService->calculateBreakdown(
                        new ProductKitting(
                            $kitting,
                            $kittingPrice
                        )
                    )->toArray()
                    : (object) []
            ];
        } else {
            $data = [
                'kitting_id' => '',
                'country_id' => $countryId,
                'name' => '',
                'code' => '',
                'is_esac' => '',
                'active' => '',
                'currency' => $currency,
                'tax' => $tax,
                'kitting_price' => [],
                'kitting_products' => [],
                'location' => [
                    'selected' => [],
                    'list' => $locations
                ],
                'description' => [],
                'images' => [
                    'list' => [],
                    'selected' => []
                ],
                'general' => [],
                'deleted_ids' => [
                    "descriptions" => [],
                    "products" => []
                ]
            ];
        }

        return $data;
    }

    /**
     * create or update kitting
     *
     * @param array $data
     * @return array|mixed
     * @throws \Exception
     */
    public function createOrUpdate(array $data)
    {
        $kitting = '';
        $errorBag = [];

        $kittingData = [
            'country_id' => $data['country_id'],
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'is_esac' => isset($data['is_esac']) ? $data['is_esac'] : 0,
            'active' => $data['active']
        ];

        //update kitting if kitting_id not null------------------------------
        if ($data['kitting_id'] != null){
            $kitting = $this->find($data['kitting_id']);

            $kitting->update($kittingData);
        } else { //create new kitting
            $kitting = $this->modelObj->create($kittingData);
        }

        //kitting Prices------------------------------------------------------------------------------------------------
        $kittingPriceData = $data['kitting_price'];

        $bouns = $kittingPriceData['bonuses'];

        $kittingData = [
            'kitting_id' => $kitting->id,
            'currency_id' => $data['currency']['id'],
            'gmp_price_gst' => $kittingPriceData['gmp_price_tax'],
            'nmp_price' => $kittingPriceData['nmp_price'],
            'rp_price' => $kittingPriceData['rp_price'],
            'rp_price_gst' => $kittingPriceData['rp_price_tax'],
            'effective_date' => $kittingPriceData['effective_date'],
            'expiry_date' => $kittingPriceData['expiry_date'],
            'base_cv' => $kittingPriceData['base_cv'],
            'wp_cv' => $kittingPriceData['wp_cv'],
            'cv1' => $kittingPriceData['cv_1'],
            'cv2' => $kittingPriceData['cv_2'],
            'cv3' => $kittingPriceData['cv_3'],
            'cv4' => $kittingPriceData['cv_4'],
            'cv5' => $kittingPriceData['cv_5'],
            'cv6' => $kittingPriceData['cv_6'],
            'welcome_bonus_l1' => $bouns['welcome_bonus_1'],
            'welcome_bonus_l2' => $bouns['welcome_bonus_2'],
            'welcome_bonus_l3'=> $bouns['welcome_bonus_3'],
            'welcome_bonus_l4' => $bouns['welcome_bonus_4'],
            'welcome_bonus_l5' => $bouns['welcome_bonus_5'],
        ];

        //kitting Price Section-----------------------------------------------------------------------------------------
        if (isset($data['kitting_price']['id']) and $data['kitting_price']['id'] > 0){
            $kittingPrice = $this->kittingPriceObj->find($data['kitting_price']['id']);

            $kittingPrice->update($kittingData);
        }else
        {
            $this->kittingPriceObj->create($kittingData);
        }

        //kitting Products Section--------------------------------------------------------------------------------------
        if (!empty($data['kitting_products'])) {

            foreach ($data['kitting_products'] as $kittingProductItem) {

                if (isset($kittingProductItem['id'])) { //update kitting product

                    $kittProduct = $this->kittingProductObj->findOrFail($kittingProductItem['id']);

                    $kittProduct->update($kittingProductItem);

                }else{ //create new kitting product

                    unset($kittingProductItem['kitting_id']);

                    $this->kittingProductObj->create(
                        array_merge( ['kitting_id' =>$kitting->id] , $kittingProductItem)
                    );
                }
            }
        }

        //kitting locations section-------------------------------------------------------------------------------------
        if (!empty($data['location']['selected'])) {

            if ($kitting->kittingLocations()->count() > 0) {
                $kitting->kittingLocations()->sync($data['location']['selected']);
            }else{
                $kitting->kittingLocations()->attach($data['location']['selected']);
            }
        }

        //kitting description-------------------------------------------------------------------------------------------
        if (!empty($data['description'])) {

            foreach ($data['description'] as $item) {

                if (isset($item['id'])) {
                    $this->KittingDescriptionObj
                        ->findOrFail($item['id'])
                        ->update($item);
                } else {
                    $this->KittingDescriptionObj->create(array_merge($item, [
                        'kitting_id' => $kitting->id
                    ]));
                }
            }
        }

        //kitting general settings--------------------------------------------------------------------------------------
        if (!empty($data['general']))
        {
            foreach ($data['general'] as $key =>  $val)
            {
                $master =  $this->masterObj->where('key', $key)->first();

                $kittingGeneralSettings =  $this->kittingGeneralSettingObj
                    ->where('master_id', $master->id)
                    ->where('kitting_id',$kitting->id)
                    ->pluck('master_data_id')
                    ->toArray();

                $selectedSettings = array_diff($val, $kittingGeneralSettings);

                if (count($selectedSettings)>0) {

                    foreach ($selectedSettings as $setting) {
                        $this->kittingGeneralSettingObj->create([
                            'kitting_id' => $kitting->id,
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
                        $this->kittingGeneralSettingObj
                            ->where('kitting_id', $kitting->id)
                            ->where('master_id', $master->id)
                            ->where( 'master_data_id', $delSetting)
                            ->delete();
                    }

                }
            }
        }

        //kitting image-------------------------------------------------------------------------------------------------
        if (!empty($data['images'])) {

            $oldIdArray = $this->kittingImagesObj
                ->where('kitting_id', $kitting->id)
                ->pluck('id')
                ->toArray();

            $oldFileArray = $this->kittingImagesObj
                ->where('kitting_id', $kitting->id)
                ->pluck('image_path')
                ->toArray();

            $newIdArray = [];

            $newFileArray = [];
            
            foreach ($data['images']['list'] as $image) {
                if (isset($image['id'])) {
                    $this->kittingImagesObj
                        ->findOrFail($image['id'])
                        ->update($image);

                    array_push($newIdArray, $image['id']); 
                } else {
                    $kittingImage = $this->kittingImagesObj->create(array_merge($image, [
                        'kitting_id' => $kitting->id
                    ]));

                    array_push($newIdArray, $kittingImage['id']); 
                }

                array_push($newFileArray, $image['image_path']);
            }

            foreach ($oldIdArray as $oldId) {
                if (!in_array($oldId, $newIdArray)) {
                    $kittingImage = $this->kittingImagesObj
                        ->findOrFail($oldId);

                    $kittingImage->delete();
                }
            }

            Uploader::synchronizeServerFile(Uploader::getUploaderSetting(true)['product_kitting_image'], $oldFileArray, $newFileArray, false);
        }

        //delete id's---------------------------------------------------------------------------------------------------
        if (!empty($data['deleted_ids'])){
            foreach ($data['deleted_ids'] as $key => $val){
                if ($key === 'descriptions'){
                    $this->KittingDescriptionObj->destroy($val);
                }

                if ($key === 'products'){
                    $this->kittingProductObj->destroy($val);
                }
            }
        }

        return array_merge([ 'errors' => $errorBag ] ,
            $this->kittingDetails($data['country_id'], $kitting->id));
    }

    /**
     * calculate total Gmp for a given kitting
     *
     * @param int $countryId
     * @param $kittingData
     * @param array $locations
     * @return float|int|mixed
     */
    public function calculateKittingTotalGmp(int $countryId, $kittingData, array $locations)
    {
        $totalGmp = 0;

        foreach ($kittingData['kitting_products'] as $product)
        {
            $effectivePrice = optional($this->productObj
                ->productEffectivePricing(
                    $countryId,
                    $product['product']->id,
                    $locations
                ))
                ->toArray();

            //fallback to active price
            if ($effectivePrice == null)
            {
                $effectivePrice = optional($this->productObj
                    ->productEffectivePricing(
                        $countryId,
                        $product['product']->id
                    ))
                    ->toArray();;
            }

            if ($product['foc_qty'] == 0) {
                $totalGmp += $effectivePrice['gmp_price_tax'] * $product['quantity'];
            }
        }

        return $totalGmp;
    }
}