<?php
namespace App\Repositories\Shop;

use App\{
    Models\Masters\MasterData,
    Models\Shop\ProductAndKitting,
    Repositories\BaseRepository,
    Repositories\Masters\MasterRepository,
    Interfaces\Shop\ShopProductAndKittingFilteringInterface,
    Interfaces\Shop\ShopItemsInterface,
    Interfaces\Sales\SaleInterface,
    Helpers\ValueObjects\ProductKitting,
    Services\Sales\CommissionService
};
use Facades\App\Helpers\Classes\Uploader;
use Config;

class ShopProductAndKittingFilteringRepository extends BaseRepository implements ShopProductAndKittingFilteringInterface
{
    private
        $masterRepositoryObj,
        $salesRepository,
        $filteredProducts,
        $countryId,
        $locationId,
        $transactionTypeConfigCodes,
        $cvAcronym,
        $salesType,
        $commissionService,
        $filteredSaleTypes = []
    ;

    public function __construct(
        ProductAndKitting $model,
        MasterRepository $masterRepository,
        SaleInterface $salesRepository,
        CommissionService $commissionService
    )
    {
        parent::__construct($model);

        $this->masterRepositoryObj = $masterRepository;

        $this->transactionTypeConfigCodes = Config('mappings.sale_types');

        $this->cvAcronym = Config('mappings.cv_acronym');

        $this->salesRepository = $salesRepository;

        $this->commissionService = $commissionService;
    }

    /**
     * Filters Products together with kitting
     *
     * @param int $countryId country Id is mandatory and used to filter the products by country
     * @param int $locationId the location id to filter the data
     * @param array $categories an array of \App\Models\Products\ProductCategory ids
     * @param array $salesTypes sales type from Master Data
     * @param int $sortBy sort by 1:Best-selling|2:Latest|3:Price-high-low|4:Price-low-high|5:CV-high-low|6:CV-low-high
     * @param int $priceMin if specified, results will be more then min price
     * @param int $priceMax if specified, results will be lesser then this
     * @param int $cvMin if specified, results will be have a grater cv then this
     * @param int $cvMax if specified, results will have lower cv then this
     * @param int $limit limit the results
     * @param int $offset offset the results
     * @param int $active only return active results
     * @param string $name the name of the product/kitting will match %LIKE%
     * @param array $with will return the results with the relationships ['description','prices','images']
     * @return $this
     */
    function filterProductAndKitting(
        int $countryId,
        int $locationId,
        array $categories,
        array $salesTypes,
        int $sortBy = null,
        int $priceMin = 0,
        int $priceMax = 0,
        int $cvMin = 0,
        int $cvMax = 0,
        int $limit = 0,
        int $offset = 0,
        int $active = 1,
        string $name = null,
        array $with = []
    )
    {
        $this->countryId = $countryId;

        $this->locationId = $locationId;
        /** @var $productsAndKitting \App\Models\Products\ProductAndKitting */
        $productsAndKitting = $this->modelObj;

        // Decide to recive active only or not
        $productsAndKitting->setActiveOnly($active);

        // the model requires country to be set
        $productsAndKitting->setCountry($countryId);

        // set the location Id to use
        $productsAndKitting->setLocation($locationId);

        //Filter by sales type is required
        $productsAndKitting->filterSalesTypes($salesTypes);

        $this->filteredSaleTypes = $salesTypes;

        (empty($name)) ?: $productsAndKitting->filterName($name);

        (empty($categories)) ?: $productsAndKitting->filterCategories($categories);

        (empty($priceMin)) ?: $productsAndKitting->priceGraterThan($priceMin);

        (empty($priceMax)) ?: $productsAndKitting->priceLessThan($priceMax);

        (empty($cvMin)) ?: $productsAndKitting->cvGraterThan($cvMin, $this->getSaleTypes());

        (empty($cvMax)) ?: $productsAndKitting->cvLessThan($cvMax, $this->getSaleTypes());

        (empty($limit)) ?: $productsAndKitting->limit($limit);

        (empty($offset)) ?: $productsAndKitting->offset($offset);

        if (!is_null($sortBy)) {
            switch (config('setting.products-kitting-sorting-order')[$sortBy]) {
                case 'best-selling': // Sort By Best Selling
                    $productsAndKitting->sortByBestSelling();
                    break;
                case 'new-to-old': // Sort By Created At Assending
                    $productsAndKitting->sortByCreatedAt('ASC');
                    break;
                case 'old-to-new': // Sort BY Created At Decending
                    $productsAndKitting->sortByCreatedAt('DESC');
                    break;
                case 'price-to-low': // Sort By Price DESC
                    $productsAndKitting->sortByPrice('DESC');
                    break;
                case 'price-to-high': // Sort by Price ASC
                    $productsAndKitting->sortByPrice('ASC');
                    break;
                case 'cv-to-low': // Sort by CV DESC
                    $productsAndKitting->sortByCv('DESC');
                    break;
                case 'cv-to-high': // Sort by CV ASC
                    $productsAndKitting->sortByCv('ASC');
                    break;
            }
        }

        // Retrieve data with
        if (is_array($with)) {
            if (in_array('images', $with)) {
                $productsAndKitting->withImages();
            }
            if (in_array('prices', $with)) {
                $productsAndKitting->withPrices();
            }
            if (in_array('descriptions', $with)) {
                $productsAndKitting->withDescriptions();
            }
            if (in_array('dummy', $with)) {
                $productsAndKitting->withDummyProducts();
            }
            if (in_array('favorites', $with)) {
                $productsAndKitting->withFavorites();
            }
        }

        $results['data'] = $productsAndKitting->get();

        $results['total'] = $productsAndKitting->count();

        $this->filteredProducts = $results;

        return $this;
    }

    /**
     * get products and kitting by ids
     *
     * @param int $countryId
     * @param int $locationId
     * @param array $productIds
     * @param array $kittingIds
     * @param int $active
     * @return $this
     * @throws \App\Exceptions\Locations\CountryAlreadySetException
     * @throws \App\Exceptions\Locations\CountryNotSetException
     * @throws \Exception
     */
    function getProductsAndKittingsByIds(
        int $countryId,
        int $locationId,
        array $productIds = [],
        array $kittingIds = [],
        int $active = 1
    )
    {
        $this->countryId = $countryId;

        $this->locationId = $locationId;

        /** @var $productsAndKitting \App\Models\Shop\ProductAndKitting */
        $productsAndKitting = $this->modelObj;

        // Decide to receive active only or not
        $productsAndKitting->setActiveOnly($active);

        // the model requires country to be set
        $productsAndKitting->setCountry($countryId);

        // set the location Id to use
        $productsAndKitting->setLocation($locationId);
        // Filter By Ids
        $productsAndKitting->filterProductId($productIds);

        $productsAndKitting->filterKittingId($kittingIds);

        $productsAndKitting->filterSalesTypes([]);

        $productsAndKitting->sortByCreatedAt('ASC');

        $productsAndKitting->withDummyProducts();

        $productsAndKitting->withImages();

        $results['data'] = $productsAndKitting->get();

        $results['total'] = $productsAndKitting->count();

        $this->filteredProducts = $results;

        return $this;
    }

    /**
     * Calculates values for the retried results
     *
     * The unit cv shows cv calculated for all sales types. The break_down only shows for those cv's that has an cv_acronym
     * in the mappings
     *
     * @return $this
     */
    public function calculated()
    {
        $salesTypeConfig = $this->transactionTypeConfigCodes;

        $saleType = $this->getSaleTypes();

        $this->filteredProducts['data'] = collect($this->filteredProducts['data'])->map(function ($product) use ($salesTypeConfig, $saleType) {
            if (isset($product['general_settings'])) {
                $breakdown = $this->commissionService->calculateBreakdown(
                    ProductKitting::fill(
                        $product,
                        $product['prices'][0],
                        !empty($product['product_id']) ? ProductKitting::PRODUCT : ProductKitting::KITTING
                    )
                );
                $product['unit_cv'] = $breakdown->toArray();
                $product['unit_cv'] = array_merge(
                    $product['unit_cv'],
                    $this->commissionService->getDefaultSaleTypeBrakeDown(
                        $breakdown,
                        count($this->filteredSaleTypes) === 1
                            ? (new MasterData)->forceFill(['id' => $this->filteredSaleTypes[0]])
                            : null
                    )
                );
            }

            return $product;
        });
        return $this;
    }

    /**
     * Refines the retrieved results
     * @return $this
     */
    public function refine() {
        $saleType = $this->getSaleTypes();
        $salesTypeConfig = $this->transactionTypeConfigCodes;

        $this->filteredProducts['data'] = collect($this->filteredProducts['data'])->map(function ($data) use ($saleType, $salesTypeConfig) {
            $data['image'] = $this->getDefaultImagePath($data);

            $data['description'] = $data['descriptions'][0] ?? null;

            $data['price'] = $data['prices'][0]['gmp_price_tax'] ?? null;

            $data['prices'] = collect($data['prices'][0])
                ->only(
                    ['gmp_price_tax', 'rp_price', 'rp_price_tax', 'nmp_price']
                )
                ->all();

            $data['favorites_id'] = $data['favorites_id'] ?? null;

            $data['products'] = $data['products_of_kitting'] ?? null;

            $data['kitting_id'] = empty($data['kitting_id']) ? null : intval($data['kitting_id']);

            $data['product_id'] = empty($data['product_id']) ? null : intval($data['product_id']);

            $data['sales_types'] = [];

            if (isset($data['general_settings'])) {
                foreach ($data['general_settings'] as $details) {
                    // check to see if master data id is a sales type
                    if (isset($saleType[$details['master_data_id']])) {
                        $saleType[$details['master_data_id']];

                        $data['sales_types'][] = [
                            "id" => $details['master_data_id'],
                            "title" => $saleType[$details['master_data_id']],
                            "key" => array_search(strtolower($saleType[$details['master_data_id']]), $salesTypeConfig)
                        ];
                    }
                }
            }

            return collect($data)->only(
                [
                    'id',
                    'product_id',
                    'kitting_id',
                    'name',
                    'sku',
                    'is_dummy_code',
                    'price',
                    'prices',
                    'unit_cv',
                    'image',
                    'description',
                    'created_at',
                    'sales_types',
                    'products',
                    'favorites_id'
                ]
            )->all();
        });

        return $this;
    }

    /**
     * Returns the sales type from the database
     * @return array
     */
    private function getSaleTypes() {
        if (is_null($this->salesType)) {
            $settingsData = $this->masterRepositoryObj->getMasterDataByKey(array('sale_types'));

            $this->salesType = $settingsData['sale_types']->pluck('title','id')->toArray();
        }
        return $this->salesType;
    }

    /**
     * Generates the image string for a product or kitting
     *
     * @param $productOrKitting data object of product or kitting
     * @return null|string with generated absolute path for image
     */
    private function getDefaultImagePath($productOrKitting) {
        if (count($productOrKitting['images']) > 0) {
            if (!empty($productOrKitting['product_id'])) {
                return Uploader::getFileLink('file', 'product_standard_image', $productOrKitting['images'][0]['image_path']);
            } else if (!empty($productOrKitting['kitting_id'])) {
                return Uploader::getFileLink('file', 'product_kitting_image', $productOrKitting['images'][0]['image_path']);
            }
        } else {
            return null;
        }
    }

    public function toArray()
    {
        return $this->filteredProducts;
    }
}
