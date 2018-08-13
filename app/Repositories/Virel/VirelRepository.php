<?php
namespace App\Repositories\Virel;

use App\{
    Interfaces\Virel\VirelInterface,
    Interfaces\Masters\MasterInterface,
    Helpers\Classes\Uploader,
    Models\Bonus\TeamBonusRank,
    Models\Currency\Currency,
    Models\Kitting\KittingProduct,
    Models\Languages\Language,
    Models\Locations\Entity,
    Models\Masters\MasterData,
    Models\Members\Member,
    Models\Products\ProductCategory,
    Models\Products\Product,
    Models\Products\ProductPrice,
    Models\Products\ProductDescription,
    Models\Kitting\Kitting,
    Models\Kitting\KittingDescription,
    Models\Kitting\KittingPrice,
    Models\Locations\Location,
    Models\Locations\City,
    Models\Locations\State,
    Models\Locations\Country,
    Models\Users\User
};
use Carbon\Carbon;

class VirelRepository implements VirelInterface
{
    private $masterRepositoryObj,
        $uploaderObj,
        $userObj,
        $memberObj,
        $teamBonusRankObj,
        $masterDataObj,
        $cityObj,
        $stateObj,
        $countryObj,
        $languageObj,
        $productCategoryObj,
        $productObj,
        $productDescriptionObj,
        $productPriceObj,
        $currencyObj,
        $kittingObj,
        $kittingProductObj,
        $kittingDescriptionObj,
        $kittingPriceObj,
        $locationObj,
        $entityObj,
        $transactionTypeConfigCodes;

    /**
     * VirelRepository constructor.
     *
     * @param MasterInterface $masterInterface
     * @param Uploader $uploader
     * @param User $user
     * @param Member $member
     * @param TeamBonusRank $teamBonusRank
     * @param MasterData $masterData
     * @param City $city
     * @param State $state
     * @param Country $country
     * @param Language $language
     * @param ProductCategory $productCategory
     * @param Product $product
     * @param ProductDescription $productDescription
     * @param ProductPrice $productPrice
     * @param Currency $currency
     * @param Kitting $kitting
     * @param KittingProduct $kittingProduct
     * @param KittingDescription $kittingDescription
     * @param KittingPrice $kittingPrice
     * @param Location $location
     * @param Entity $entity
     */
    public function __construct(
        MasterInterface $masterInterface,
        Uploader $uploader,
        User $user,
        Member $member,
        TeamBonusRank $teamBonusRank,
        MasterData $masterData,
        City $city,
        State $state,
        Country $country,
        Language $language,
        ProductCategory $productCategory,
        Product $product,
        ProductDescription $productDescription,
        ProductPrice $productPrice,
        Currency $currency,
        Kitting $kitting,
        KittingProduct $kittingProduct,
        KittingDescription $kittingDescription,
        KittingPrice $kittingPrice,
        Location $location,
        Entity $entity
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->uploaderObj = $uploader;

        $this->userObj = $user;

        $this->memberObj = $member;

        $this->teamBonusRankObj = $teamBonusRank;

        $this->masterDataObj = $masterData;

        $this->cityObj = $city;

        $this->stateObj = $state;

        $this->countryObj = $country;

        $this->languageObj = $language;

        $this->productCategoryObj = $productCategory;

        $this->productObj = $product;

        $this->productDescriptionObj = $productDescription;

        $this->productPriceObj = $productPrice;

        $this->currencyObj = $currency;

        $this->kittingObj = $kitting;

        $this->kittingProductObj = $kittingProduct;

        $this->kittingDescriptionObj = $kittingDescription;

        $this->kittingPriceObj = $kittingPrice;

        $this->locationObj = $location;

        $this->entityObj = $entity;

        $this->transactionTypeConfigCodes = config('mappings.sale_types');
    }

    /**
     * get user by email
     *
     * @param string $email
     * @param int $old_member_id
     * @return mixed
     */
    public function getUser(string $email = null, int $old_member_id = null)
    {
        $user = null;

        if (isset($email)) {
            $user = $this->userObj->where('email', '=', $email)->first();
        }
        else if(isset($old_member_id)) {
            $user = $this->userObj->where('old_member_id', '=', $old_member_id)->first();
        }
        
        if (isset($user)) {
            return [
                'id' => $user->id,
                'old_member_id' => $user->old_member_id,
                'old_ibs_user_id' => $user->old_ibs_user_id
            ];
        }
        else {
            return [
                'id' => null,
                'old_member_id' => null,
                'old_ibs_user_id' => null
            ];
        }
    }

    /**
     * get member by old_member_id
     *
     * @param int $old_member_id
     * @return mixed
     */
    public function getMember(int $old_member_id)
    {
        $user = $this->userObj->where('old_member_id', '=', $old_member_id)->first();

        $member = $this->memberObj->where('user_id', '=', $user->id)->first();

        $personalData = optional($member->personalData())->first();

        $contactData = optional($member->contactInfo())->first();

        $addressData = optional($member->address())->first();

        $mappingData = array(
            'gender' => null,
            'address1' => null,
            'address2' => null,
            'postcode' => null,
            'city' => null,
            'state' => null,
            'country' => null,
            'companyCode' => null,
            'prefLanguage' => null,
            'lastUpdatedOn' => null,
            'effectiveRank' => null,
            'highestRank' => null
        );

        if (isset($member->effective_rank_id)) {
            $effectiveRank = $this->teamBonusRankObj->find($member->effective_rank_id);

            if (isset($effectiveRank)) {
                $mappingData['effectiveRank'] = $effectiveRank->rank_code;
            }
        }

        if (isset($member->highest_rank_id)) {
            $highestRank = $this->teamBonusRankObj->find($member->highest_rank_id);

            if (isset($highestRank)) {
                $mappingData['highestRank'] = $highestRank->rank_code;
            }
        }

        if (isset($personalData)) {
            if (isset($personalData->gender_id)) {
                $gender = $this->masterDataObj->find($personalData->gender_id);

                if (isset($gender)) {
                    $mappingData['gender'] = $gender->title;
                }
            }
        }

        if (isset($addressData) && isset($addressData->address_data)) {
            $addressData = json_decode($addressData->address_data, true);

            $permanentAddress = null;

            foreach ($addressData as $addressComponent) {
                if (isset($addressComponent['title'])) {
                    if ($addressComponent['title'] == 'Permanent' && count($addressComponent['fields']) > 0) {
                        $permanentAddress = $addressComponent;
                        break;
                    } else if (count($addressComponent['fields']) > 0) {
                        $permanentAddress = $addressComponent;
                    }
                }
            }

            if (isset($permanentAddress)) {
                foreach ($permanentAddress['fields'] as $addressField) {
                    switch (strtolower($addressField['label'])) {
                        case 'address 1':
                            $mappingData['address1'] = $addressField['value'];
                            break;

                        case 'address 2':
                            $mappingData['address2'] = $addressField['value'];
                            break;

                        case 'postcode':
                            $mappingData['postcode'] = $addressField['value'];
                            break;

                        case 'city':
                            if (isset($addressField['value'])) {
                                $city = $this->cityObj->find($addressField['value']);

                                if (isset($city)) {
                                    $mappingData['city'] = $city->name;
                                }
                            }
                            break;

                        case 'state':
                            if (isset($addressField['value'])) {
                                $state = $this->stateObj->find($addressField['value']);

                                if (isset($state)) {
                                    $mappingData['state'] = $state->name;
                                }
                            }
                            break;

                        case 'country':
                            if (isset($addressField['value'])) {
                                $country = $this->countryObj->find($addressField['value']);

                                if (isset($country)) {
                                    $mappingData['country'] = $country->name;
                                }
                            }
                            break;
                    }
                }
            }
        }

        if (isset($member->country_id)) {
            $country = $this->countryObj->find($member->country_id);

            if (isset($country)) {
                $mappingData['companyCode'] = $country->code_iso_2;
            }
        }

        if (isset($personalData)) {
            if (isset($personalData->language_id)) {
                $language = $this->languageObj->find($personalData->language_id);

                if (isset($language)) {
                    $mappingData['prefLanguage'] = $language->name;
                }
            }
        }

        if (isset($member->updated_at)) {
            $mappingData['lastUpdatedOn'] = date_format($member->updated_at, "Y-m-d H:i:s");
        }

        return [
            'id' => $user->old_ibs_user_id,
            'name' => $member->name,
            'gender' => $mappingData['gender'],
            'ename' => $member->translated_name,
            'dob' => $member->date_of_birth,
            'idno' => $member->ic_passport_number,
            'address1' => $mappingData['address1'],
            'address2' => $mappingData['address2'],
            'address3' => '',
            'address4' => '',
            'postcode' => $mappingData['postcode'],
            'city' => $mappingData['city'],
            'state' => $mappingData['state'],
            'country' => $mappingData['country'],
            'mobile1' => optional($contactData)->mobile_1_num,
            'email' => $user->email,
            'memberid' => $user->old_member_id,
            'member_type' => 'DIST',
            'member_subtype' => 'DIST',
            'price_code' => 'GMP',
            'company_code' => $mappingData['companyCode'],
            'pref_language' => $mappingData['prefLanguage'],
            'eff_bonus_rank' => $mappingData['effectiveRank'],
            'highest_bonus_rank' => $mappingData['highestRank'],
            'last_updated_on' => $mappingData['lastUpdatedOn']
        ];
    }

    /**
     * get product category list
     *
     * @return mixed
     */
    public function getProductCategories()
    {
        return $this->productCategoryObj->all()->map(function ($productCategory) {
            return [
                'id' => $productCategory->id,
                'parent_id' => $productCategory->parent_id,
                'name' => $productCategory->name
            ];
        });
    }

    /**
     * get standard product list
     *
     * @return mixed
     */
    public function getProducts()
    {
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_types'
            )
        );

        $saleType = array_change_key_case(
            $masterSettingsDatas['sale_types']
                ->pluck('id', 'title')
                ->toArray()
        );

        $repurchaseSaleTypeId = $saleType[$this->transactionTypeConfigCodes['repurchase']];

        $rentalSaleTypeId = $saleType[$this->transactionTypeConfigCodes['rental']];

        $uploaderSetting = $this->uploaderObj->getUploaderSetting(true);

        $productImageSetting = $uploaderSetting['product_standard_image'];

        $kittingImageSetting = $uploaderSetting['product_kitting_image'];

        // PRODUCT RECORDS
        $products = $this->productObj
            ->with([
                'productNames.country', 
                'productDescriptions.language', 
                'productImages.country', 
                'productRentalPlan'
            ])
            ->whereExists(function ($query) use ($repurchaseSaleTypeId, $rentalSaleTypeId) {
                $query->select('id')
                      ->from('product_general_settings')
                      ->whereRaw('product_general_settings.product_id = products.id')
                      ->whereIn('master_data_id', [$repurchaseSaleTypeId, $rentalSaleTypeId]);
            })
            ->whereExists(function ($query) {
                $query->select('product_prices.id')
                      ->from('product_prices')
                      ->where('product_prices.effective_date','<=', Carbon::now())
                      ->where('product_prices.expiry_date','>=', Carbon::now())
                      ->where('product_prices.promo', 0)
                      ->whereRaw('product_prices.product_id = products.id');
            })
            ->get()
            ->map(function ($product) use ($productImageSetting) {
                $productPrices = $this->productPriceObj
                    ->with(['currency', 'country'])
                    ->where('product_id', '=', $product->id)
                    ->where('effective_date','<=', Carbon::now())
                    ->where('expiry_date','>=', Carbon::now())
                    ->where('promo', '=', 0)
                    ->get()
                    ->groupBy('country_id')
                    ->map(function ($productPrices) use ($product) {
                        $productPrice = $productPrices->sortByDesc('id')->first();

                        $rentalPlan = $product->productRentalPlan
                            ->where('country_id', $productPrice->country_id)
                            ->map(function ($productRentalPlan) {
                                return [
                                    'initial_payment' => $productRentalPlan->initial_payment,
                                    'monthly_repayment' => $productRentalPlan->monthly_repayment,
                                    'total_payment' => $productRentalPlan->total_payment,
                                    'tenure' => $productRentalPlan->tenure,
                                    'number_of_cw' => $productRentalPlan->number_of_cw
                                ];
                            })
                            ->first();
                        
                        return [
                            'id' => $productPrice->id,
                            'currency_code' => $productPrice->currency->code,
                            'country_code' => $productPrice->country->code_iso_2,
                            'gmp_price_gst' => $productPrice->gmp_price_gst,
                            'rp_price_gst' => $productPrice->rp_price_gst,
                            'effective_date' => $productPrice->effective_date,
                            'expiry_date' => $productPrice->expiry_date,
                            'rental_plan' => $rentalPlan
                        ];
                    });
                    
                $prices = array();

                foreach($productPrices as $countryId => $productPrice) {
                    $prices[] = $productPrice;
                }

                $images = array();

                foreach ($product->productImages as $key => $productImage) {
                    $images[] = [
                        'country_code' => $productImage->country->code_iso_2,
                        'image_link' => $productImage->image_link,
                        'max_height' => 0,
                        'max_width' => 0
                    ];

                    if (count($productImageSetting['resize_image']) > 0) {
                        foreach ($productImageSetting['resize_image'] as $resizeImage) {
                            $tempFile = $this->uploaderObj->getFileNameWithPrefixSuffix($productImage->image_link, $resizeImage['prefix'], $resizeImage['suffix']);
                            $images[] = [
                                'country_code' => $productImage->country->code_iso_2,
                                'image_link' => $tempFile,
                                'max_height' => $resizeImage['max_height'],
                                'max_width' => $resizeImage['max_width']
                            ];
                        }
                    }
                }

                return [
                    'product_id' => $product->id,
                    'kitting_id' => null,
                    'name' => $product->name,
                    'names' => $product->productNames->map(function ($productName) {
                        return [
                            'country_code' => $productName->country->code_iso_2,
                            'name' => $productName->name
                        ];
                    }),
                    'sku' => $product->sku,
                    'category_id' => $product->category_id,
                    'deleted_at' => $product->deleted_at,
                    'descriptions' => $product->productDescriptions->map(function ($productDescription) {
                        return [
                            'id' => $productDescription->id,
                            'language_name' => $productDescription->language->name,
                            'locale_code' => $productDescription->language->locale_code,
                            'marketing_description' => $productDescription->marketing_description,
                            'benefits' => $productDescription->benefits,
                            'specification' => $productDescription->specification
                        ];
                    }),
                    'prices' => $prices,
                    'images' => $images
                ];
            })
            ->toArray();
        
        // KITTING RECORDS
        $kittings = $this->kittingObj
            ->with([
                'country',
                'kittingPrice.currency',
                'kittingDescriptions.language', 
                'kittingImages'
            ])
            ->where('is_esac', 0)
            ->where('active', 1)
            ->whereExists(function ($query) use ($repurchaseSaleTypeId, $rentalSaleTypeId) {
                $query->select('kitting_general_settings.kitting_id')
                    ->from('kitting_general_settings')
                    ->whereRaw('kitting_general_settings.kitting_id = kitting.id')
                    ->whereIn('master_data_id', [$repurchaseSaleTypeId, $rentalSaleTypeId]);
            })
            ->whereHas('kittingPrice.currency')
            //TODO: revise pricing
            // ->whereExists(function ($query) { 
            //     $query->select('kitting_prices.id')
            //         ->from('kitting_prices')
            //         ->where('kitting_prices.effective_date','<=', Carbon::now())
            //         ->where('kitting_prices.expiry_date','>=', Carbon::now())
            //         ->where('kitting_prices.active', 1)
            //         ->whereRaw('kitting_prices.kitting_id = kitting.id');
            // })
            ->get()
            ->map(function ($kitting) use ($kittingImageSetting) {
                $kittingPrice = $kitting->kittingPrice;
                    
                $prices = [
                    'id' => $kittingPrice->id,
                    'currency_code' => $kittingPrice->currency->code,
                    'country_code' => $kitting->country->code_iso_2,
                    'gmp_price_gst' => $kittingPrice->gmp_price_gst,
                    'rp_price_gst' => $kittingPrice->rp_price_gst,
                    'effective_date' => $kittingPrice->effective_date,
                    'expiry_date' => $kittingPrice->expiry_date,
                    'rental_plan' => null
                ];

                $images = array();

                foreach ($kitting->kittingImages as $key => $kittingImage) {
                    $images[] = [
                        'country_code' => $kitting->country->code_iso_2,
                        'image_link' => $kittingImage->image_link,
                        'max_height' => 0,
                        'max_width' => 0
                    ];

                    if (count($kittingImageSetting['resize_image']) > 0) {
                        foreach ($kittingImageSetting['resize_image'] as $resizeImage) {
                            $tempFile = $this->uploaderObj->getFileNameWithPrefixSuffix($kittingImage->image_link, $resizeImage['prefix'], $resizeImage['suffix']);
                            $images[] = [
                                'country_code' => $kitting->country->code_iso_2,
                                'image_link' => $tempFile,
                                'max_height' => $resizeImage['max_height'],
                                'max_width' => $resizeImage['max_width']
                            ];
                        }
                    }
                }

                return [
                    'product_id' => null,
                    'kitting_id' => $kitting->id,
                    'name' => $kitting->name,
                    'names' => [
                        'country_code' => $kitting->country->code_iso_2,
                        'name' => $kitting->name
                    ],
                    'sku' => $kitting->code,
                    'category_id' => null,
                    'deleted_at' => $kitting->deleted_at,
                    'descriptions' => $kitting->kittingDescriptions->map(function ($kittingDescription) {
                        return [
                            'id' => $kittingDescription->id,
                            'language_name' => $kittingDescription->language->name,
                            'locale_code' => $kittingDescription->language->locale_code,
                            'marketing_description' => $kittingDescription->marketing_description,
                            'benefits' => null,
                            'specification' => null
                        ];
                    }),
                    'prices' => $prices,
                    'images' => $images
                ];
            })
            ->toArray();

        return array_merge($products, $kittings);
    }

    /**
     * get promo product list
     *
     * @return mixed
     */
    public function getPromoProducts()
    {
        return $this->kittingObj->all()->map(
            function ($kitting) {
                $kittingProduct = $this->kittingProductObj->where('kitting_id', '=', $kitting->id)->where('quantity', '>', 0)->get();

                $product = $this->productObj->whereIn('id', $kittingProduct->pluck('product_id')->toArray())->get();

                $category = $this->productCategoryObj->whereIn('id', $product->pluck('category_id')->toArray())->get()->first();

                return [
                    'id' => $kitting->id,
                    'name' => $kitting->name,
                    'sku' => $kitting->code,
                    'category_id' => $category->id,
                    'deleted_at' => $kitting->deleted_at,
                    'descriptions' => $this->kittingDescriptionObj->where('kitting_id', '=', $kitting->id)->get()->map(
                        function ($kittingDescription) {
                            $language = $this->languageObj->find($kittingDescription->language_id);

                            return [
                                'id' => $kittingDescription->id,
                                'language_name' => $language->name,
                                'marketing_description' => $kittingDescription->marketing_description,
                                'benefits' => $kittingDescription->benefits,
                                'specification' => $kittingDescription->specification
                            ];
                        }
                    ),
                    'prices' => $this->kittingPriceObj->where('kitting_id', '=', $kitting->id)->get()->map(
                        function ($kittingPrice) use ($kitting) {
                            $currency = $this->currencyObj->find($kittingPrice->currency_id);

                            $country = $this->countryObj->where('id', $kitting['country_id'])->first();

                            return [
                                'id' => $kittingPrice->id,
                                'currency_code' => $currency->code,
                                'country_code' => $country->code_iso_2,
                                'gmp_price_gst' => $kittingPrice->gmp_price_gst,
                                'rp_price_gst' => $kittingPrice->rp_price_gst,
                                'effective_date' => $kittingPrice->effective_date,
                                'expiry_date' => $kittingPrice->expiry_date
                            ];
                        }
                    )
                ];
            }
        );
    }
}