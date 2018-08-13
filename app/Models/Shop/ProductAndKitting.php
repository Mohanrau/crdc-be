<?php
namespace App\Models\Shop;

use App\Models\Products\{
    Product,
    ProductPrice,
    ProductPromoLocation,
    ProductImage,
    ProductDescription,
    ProductGeneralSetting,
    ProductCategory,
    ProductActive,
    ProductLocation
};
use App\Models\Kitting\{
    KittingPrice,
    Kitting,
    KittingImage,
    KittingDescription,
    KittingProduct,
    KittingGeneralSetting
};
use App\Exceptions\Locations\{
    CountryNotSetException,
    CountryAlreadySetException,
    LocationAlreadySetException
};
use App\Models\Locations\Location;
use App\Models\Dummy\Dummy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App;
use DB;
use Auth;

class ProductAndKitting extends Model
{
    private
        $productQuery,
        $productQueryFilter,
        $kittingQuery,
        $kittingQueryFilter,
        $onlyActive = true,
        $limit = 0,
        $offset = 0,
        $country,
        $buildStated = false,
        $location,
        $addToUnionTypes = [
        "both",
        "filters",
        "results"
    ],
        $withPriceSet = false,
        $isKittingJoinedWithProducts = false,
        $orderTypes = [
        "ASC",
        "DESC"
    ],
        $orders = [],
        $productsModel,
        $kittingModel,
        $tableNames,
        $filteredSalesTypesIds = [],
        $cvFiltered = false,
        $salesTypes = null,
        $cvAcronymCodes,
        $cvMechanism,
        $saleTypeCvSettings,
        $transactionTypeConfigCodes,
        $saleTypeCvFields = []
    ;

    /**
     * ProductAndKitting constructor.
     *
     * Initializes names and creates model objects
     *
     * {@inheritdoc}
     */
    public function __construct (array $attributes = [])
    {
        parent::__construct($attributes);

        // Define Models
        $this->productsModel = App::make(Product::class);

        $this->kittingModel = App::make(Kitting::class);

        $this->initializeTableNames();

        // Initiate query builder
        $this->productQuery = DB::table($this->tableNames->product->name);

        $this->kittingQuery = DB::table($this->tableNames->kitting->name);

        $this->cvAcronymCodes = config('mappings.cv_acronym');

        $this->saleTypeCvSettings = config('setting.sale-type-cvs');

        $this->transactionTypeConfigCodes = config('mappings.sale_types');

        $this->cvMechanism = config('setting.cv-mechanism');
    }

    /**
     * To retrieve active data or not
     *
     * @param bool $state the state to set
     * @throws \Exception if the joins has already been created
     */
    public function setActiveOnly (bool $state)
    {
        if (!$this->buildStated) {
            $this->onlyActive = $state;
        } else {
            throw new \Exception(trans("message.query-building.active-status-cannot-change"));
        }
    }

    /**
     * Set the country for with the kitting should be retrieved
     *
     * @param $country id of @see \App\Models\Locations\Country::$id
     * @throws CountryAlreadySetException
     * @throws \Exception
     */
    public function setCountry ($country)
    {
        if (!isset($this->country)) {
            $this->country = $country;

            $prodcutCountryTableName = $this->tableNames->product->country->name;

            // Filter products by country
            $this->buildJoin(
                "product", "country", 'product_id',
                function ($join) use ($prodcutCountryTableName, $country) {
                    $join->where($prodcutCountryTableName . ".country_id", "=", $country);
                }, null
            );

            // Join all but filter only ibs active
            $this->productQuery->where($this->tableNames->product->country->name . ".ibs_active", 1);

            // Filter kittings by country
            $this->kittingQuery->where($this->tableNames->kitting->name . ".country_id", "=", $country);

            if ($this->onlyActive) {
                $this->kittingQuery->where($this->tableNames->kitting->name . ".active", "=", 1);
            }

            // build defaults
            $this->buildDefaults();
        } else {
            throw new CountryAlreadySetException(trans("message.country.already-set"));
        }
    }

    /**
     * Set the location of the requesting data
     *
     * @param $locationId int
     * @throws LocationAlreadySetException
     * @throws \Exception
     */
    public function setLocation (int $locationId)
    {
        if (!isset($this->location)) {
            if (!isset($this->country)) {
                throw new CountryNotSetException(trans("message.country.not-set"));
            }
            $this->location = $locationId;

            foreach (
                [
                    "product",
                    "kitting"
                ] as $table
            ) {
                $this->buildJoin(
                    $table,
                    $table . "_locations",
                    $table . '_id',
                    null,
                    null,
                    'id',
                    'leftJoin',
                    'filters'
                );

                $tableName = '';

                $this->buildJoin(
                    [
                        $table . '_locations',
                        $table
                    ],
                    "locations",
                    'id',
                    function ($join, $from, $to, $union, $as) use (&$tableName) { $tableName = $to; },
                    null,
                    'location_id',
                    'leftJoin',
                    'filters'
                );

                $this->{$table . "QueryFilter"}->where(function ($q) use ($tableName, $locationId) {
                    $q->where($tableName . ".id", $locationId);
                    $q->where($tableName . ".active", 1);
                });
            }
        } else {
            throw new LocationAlreadySetException(trans("message.location.already-set"));
        }
    }

    /**
     * Filter by name of the product or kitting
     *
     * @param $name string name to filter
     */
    public function filterName ($name)
    {
        $nameFilter = $this->buildCommonWhere("name", "where", "LIKE", "%{$name}%", false);

        $skuFilter = $this->buildCommonWhere("sku", "where", "LIKE", "%{$name}%",false);

        $codeFilter = $this->buildCommonWhere("code", "where", "LIKE", "%{$name}%", false);

        $filters = collect($nameFilter)
            ->merge($skuFilter)
            ->merge($codeFilter)
            ->groupBy(['table', 'union'])
            ->toArray();

        foreach ($filters as $table => $filter) {
            foreach ($filter as $union => $builds) {
                $this->{$table . "Query" . $union}->where(function ($query) use ($builds) {
                    $type = 'where';

                    foreach ($builds as $build) {
                        $query->{$type}($build['tableName']->name . "." . $build['field'], $build['condition'], $build['value']);

                        $type = 'orWhere';
                    }
                });
            }
        }

    }

    /**
     * Process the query builder to include join array for prices
     */
    public function withPrices ()
    {
        if (!$this->withPriceSet) {
            $this->withPriceSet = true;

            $countryId = $this->getCountryId();

            $locationId = $this->getLocation();

            // add select statements
            $this->buildSelectFromFillable("prices");

            // Create joins to prices table
            foreach (
                [
                    'product',
                    'kitting'
                ] as $table
            ) {
                $this->buildJoin(
                    $table, "prices", $table . '_id',
                    function ($join, $fromTable, $toTable, $unionName) use ($countryId, $locationId, $table) {
                        if ($table == 'product') {
                            $join->where($toTable . '.country_id', "=", $countryId);
                        }

                        // verify if the join is in the current date time
                        $join->where($toTable . '.effective_date', '<=', Carbon::now());

                        $join->where($toTable . '.expiry_date', '>=', Carbon::now());
                    }, ($table == 'product' ? 'active' : null), "id", "join"
                ); // use inner join to show only products with a valid date, only product prices active field check is valid
            }

            $this->buildJoin(
                'product', "promoLocation", 'promo_id',
                function ($join, $fromTable, $toTable, $unionName) use ($countryId, $locationId) {
                    $join->where($toTable . '.location_id', "=", $locationId);
                }, null, "id", "leftJoin"
            );

            $this->buildOrder('DESC', "prices_id", 90);

            $this->buildOrder('DESC', "prices_promo", 100);
        }
    }

    /**
     * Results with Favorites
     *
     */
    public function withFavorites ()
    {

        $joinToTable = "favorites";
        // add select statements
        $this->buildSelectFromFillable($joinToTable);

        // process favorites join
        foreach (
            [
                'product',
                'kitting'
            ] as $table
        ) {
            $this->buildJoin(
                $table, $joinToTable, $table . '_id',
                function ($join, $fromTable, $toTable, $unionName) use ($table) {
                    $join->where($toTable . '.user_id', Auth::id());
                }, null
            );
        }
    }

    /**
     * Results with images
     *
     * @throws CountryNotSetException
     * @throws \Exception
     */
    public function withImages ()
    {

        $countryId = $this->getCountryId();

        $joinToTable = "images";

        // add select statements
        $this->buildSelectFromFillable($joinToTable);

        // process images join
        foreach (
            [
                'product',
                'kitting'
            ] as $table
        ) {
            $this->buildJoin(
                $table, $joinToTable, $table . '_id',
                function ($join, $fromTable, $toTable, $unionName) use ($countryId, $table) {
                    if ($table == 'product') {
                        $join->where($toTable . '.country_id', "=", $countryId);
                    }

                    $join->where($toTable . '.default', "=", 1);
                }, 'active', "id", "leftJoin", "results"
            );
        }
    }

    /**
     * Results with descriptions
     *
     * @throws \Exception
     */
    public function withDescriptions ()
    {
        $joinToTable = "descriptions";

        // add select statements
        $this->buildSelectFromFillable($joinToTable);

        // Build the joins
        foreach (
            [
                'product',
                'kitting'
            ] as $table
        ) {
            $this->buildJoin(
                $table, $joinToTable, $table . '_id', null, 'active', 'id', 'leftJoin', "results"
            );
        }
    }

    /**
     * Results with dummy products
     *
     * @throws \Exception
     */
    public function withDummyProducts ()
    {
        $this->joinKittingToProducts();

        $countryId = $this->getCountryId();

        // dummy product
        $dummyProductsTable = null;

        $dummyFromTable = null;

        $dummyTable = null;

        foreach (
            [
                "product" => [
                    "product",
                    [
                        "dummies_products",
                        "product"
                    ]
                ],
                "kitting" => [
                    [
                        "products_of_kitting",
                        "kitting"
                    ],
                    [
                        "dummies_of_kitting_product",
                        "kitting"
                    ]
                ]
            ] as $queryName => $variables
        ) {

            list
                (
                $fromDummyProductsTable, $fromDummyTable
                ) = $variables;

            // join product to dummies products
            $this->buildJoin(
                $fromDummyProductsTable, "dummies_products", 'product_id',
                function ($join, $fromTable, $toTable, $union, $as) use (&$dummyProductsTable, &$dummyFromTable) {
                    $dummyProductsTable = $as != false ? $as : $toTable;

                    $dummyFromTable = $fromTable;
                }, '', "id", "leftJoin", "both"
            );

            // join dummies products to dummy
            $this->buildJoin(
                $fromDummyTable, "dummy", 'id', function ($join, $fromTable, $toTable) use (&$dummyTable) {
                    $dummyTable = $toTable;
                }, 'active', 'dummy_id', "leftJoin", "both"
            );

            // Get products when dummy id is null or when dummy id is not null, it should have a dummy code and
            // should be a lingerie item
            $this->{$queryName . "QueryFilter"}->where(
                function ($q) use (
                    $dummyProductsTable, $dummyTable, $dummyFromTable, $countryId
                ) {
                    // Show products which is dummy code 1 and has a dummy relationship to dummy where it is_lingerie 1
                    $q->where(
                        function ($q2) use ($dummyProductsTable, $dummyTable, $dummyFromTable, $countryId) {
                            $q2->where($dummyFromTable . ".is_dummy_code", 1);

                            $q2->whereNotNull($dummyProductsTable . ".dummy_id");

                            $q2->where($dummyTable . ".is_lingerie", 1);

                            $q2->where($dummyTable . ".country_id", $countryId);
                        }
                    );

                    // Show products which has a relationship to dummy products but dose not belong to the country
                    $q->orWhere(
                        function ($q2) use ($dummyProductsTable, $dummyFromTable, $countryId, $dummyTable) {
                            $q2->where($dummyFromTable . ".is_dummy_code", 0);

                            $q2->whereNotNull($dummyProductsTable . ".dummy_id");

                            $q2->whereNotNull($dummyTable . ".country_id", '!=', $countryId);
                        }
                    );

                    // Show products which dose not have a dummy relationship
                    $q->orWhere(
                        function ($q2) use ($dummyProductsTable, $dummyFromTable) {
                            $q2->where($dummyFromTable . ".is_dummy_code", 0);

                            $q2->whereNull($dummyProductsTable . ".dummy_id");
                        }
                    );
                }
            );
        }
    }

    /**
     * TODO: implement sort by best selling
     * @return $this
     */
    public function sortByBestSelling ()
    {
        // TODO: implement
        return $this;
    }

    /**
     * Sort by created at
     *
     * @param string $type ASC|DESC
     * @throws \Exception
     */
    public function sortByCreatedAt ($type)
    {
        $this->buildOrder($type, "created_at");
    }

    /**
     * Sort by best price
     *
     * @param $type ASC|DESC
     * @throws \Exception
     */
    public function sortByPrice ($type)
    {
        $this->buildOrder($type, "prices_gmp_price_gst");
    }

    /**
     * Sort by CV
     *
     * @param $type ASC|DESC
     * @throws \Exception
     */
    public function sortByCv ($type)
    {
        $reverseOrder = current(array_diff($this->orderTypes, [$type]));

        $this->buildOrder($type, DB::raw("prices_base_cv = 0"));

        $this->buildOrder($type, DB::raw("prices_cv1 = 0"));

        $this->buildOrder($type, DB::raw("prices_wp_cv = 0"));

        $this->buildOrder($type, "prices_base_cv");

        $this->buildOrder($type, "prices_cv1");

        $this->buildOrder($type, "prices_wp_cv");
    }

    /**
     * Limit the query
     *
     * @param int $limit
     * @return $this
     */
    public function limit (int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Offset the results
     *
     * @param int $offset
     * @return $this
     */
    public function offset (int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Filter by Categories
     *
     * Shows products and kitting for the category id
     * three levels from the category ids
     *
     * @param array $categoriesIds ids of categories
     */
    public function filterCategories (array $categoriesIds)
    {
        $this->joinKittingToProducts();

        // Get all categories that has the categoryIds in id and parent
        $parentCategoriesQuery = DB::table((new ProductCategory())->getTable());

        $parentCategoriesQuery->select('id');

        $parentCategoriesQuery->where(
            function ($query) use ($categoriesIds) {
                $query->whereIn('id', $categoriesIds);

                $query->orWhereIn('parent_id', $categoriesIds);
            }
        );

        $parentCategoriesQuery->where('active', 1);

        // Get all categories which has parent categories in id and parent_id
        $categoriesQuery = DB::table((new ProductCategory())->getTable());

        $categoriesQuery->select('id');

        $categoriesQuery->where(
            function ($query) use ($parentCategoriesQuery) {
                $query->whereIn('id', clone $parentCategoriesQuery);

                $query->orWhereIn('parent_id', clone $parentCategoriesQuery);
            }
        );

        // Add where statement and move bindings to the main queries
        foreach (
            [
                [
                    "product",
                    "{$this->tableNames->product->name}.category_id"
                ],
                [
                    "kitting",
                    "products_of_kitting.category_id"
                ],
            ] as $proceed
        ) {
            list ( $table, $field ) = $proceed;

            $this->{$table . "QueryFilter"}->whereIn($field, clone $categoriesQuery);
        }
    }

    /**
     * Filter Sales Types
     *
     * Filters query based on the sales types. This filter will return products with filtered sales type and will only
     * display based on the sales type. eg. If the product has sales type a,b,c and is filtered by a,b then it will return
     * only results for a,b and also the product sales type will have a,b only
     *
     * @param array $salesTypes array of sales type ids
     * @throws CountryNotSetException
     * @throws \Exception
     */
    public function filterSalesTypes (array $salesTypes)
    {
        // add select statements
        $countryId = $this->getCountryId();

        if ($this->cvFiltered) {
            throw new \Exception(trans("message.cv.cannot-filter-sales-type-after-cv"));
        } else {
            $this->buildSelectFromFillable("general_settings");

            $joinToTable = "general_settings";

            foreach (
                [
                    'product',
                    'kitting'
                ] as $table
            ) {
                $this->buildJoin(
                    $table, $joinToTable, $table . '_id',
                    function ($join, $fromTable, $toTable, $unionName) use ($countryId, $table, $salesTypes) {
                        if ($table == 'product') {
                            $join->where($toTable . '.country_id', "=", $countryId);
                        }
                        if (!empty($salesTypes)) {
                            $join->whereIn($toTable . '.master_data_id', $salesTypes);
                        }
                    }, null, "id", "join", "both"
                ); // use inner join to show only products with a valid sales types

            }

            $this->filteredSalesTypesIds = $salesTypes;
        }
    }

    /**
     * Filter by Product Id
     *
     * @param array $productIds
     */
    public function filterProductId (array $productIds)
    {
        $this->withPrices();

        $this->productQueryFilter->whereIn($this->tableNames->product->name . ".id", $productIds);

        $this->limit = $this->limit + count($productIds);
    }

    /**
     * Filter by Kitting Id
     *
     * @param array $kittingIds
     */
    public function filterKittingId (array $kittingIds)
    {
        $this->withPrices();

        $this->kittingQueryFilter->whereIn($this->tableNames->kitting->name . ".id", $kittingIds);

        $this->limit = $this->limit + count($kittingIds);
    }

    /**
     * Filter query for prices grater than
     *
     * @param $price double
     */
    public function priceGraterThan ($price)
    {
        $this->buildCommonWhere("gmp_price_gst", "where", ">=", $price,true, "prices");
    }

    /**
     * Filter query for prices less than
     *
     * @param $price double
     */
    public function priceLessThan ($price)
    {
        $this->buildCommonWhere("gmp_price_gst", "where", "<=", $price, true, "prices");
    }

    /**
     * Filter query for cv grater than
     *
     * @param $cv integer
     */
    public function cvGraterThan (int $cv, array $masterDataSalesTypeIds)
    {
        $this->cvQuery($cv, $masterDataSalesTypeIds, '>=');
    }

    /**
     * Filter query for cv less than
     *
     * @param $cv integer
     * @param $masterDataSalesTypeIds
     */
    public function cvLessThan (int $cv, array $masterDataSalesTypeIds)
    {
        $this->cvQuery($cv, $masterDataSalesTypeIds, '<=');
    }

    /**
     * Create a new instance
     *
     * @param array $attributes
     * @param bool $exists
     *
     * @return ProductAndKitting
     */
    public function newInstance ($attributes = [], $exists = false)
    {
        return parent::newInstance($attributes, $exists);
    }

    /**
     * Clones the product and kitting results query and returns a union query
     * The cloning is to protect the query being changed from elsewhere
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function getResultUnion ()
    {
        // we return a clone to protect the query from changing
        $productResults = clone $this->productQuery;

        $kittingResults = clone $this->kittingQuery;

        $union = $productResults->union($this->kittingQuery);

        ksort($this->orders);

        foreach ($this->orders as $order) {
            $union->orderBy(current($order), key($order));
        }

        return $union;
    }

    /**
     * Clones the product and kitting Filter query and returns a union query
     * The cloning is to protect the query being changed from elsewhere
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function getFilterUnion ()
    {
        $kittingFilter = clone $this->kittingQueryFilter;

        $kittingFilter->groupBy('kitting_id');

        $productFilter = clone $this->productQueryFilter;

        $productFilter->groupBy('product_id');

        return $productFilter->union($kittingFilter);
    }

    /**
     * Unify the union
     *
     * To execute a single query to retrieve all the related records, the filtering and ordering of the query needs
     * to be done without the left joins. Filtering done with the left join will filter with the left joins. Because
     * the joins has one to many and many to many records, we filter the main query with the filtering query in the
     * where clause using the following format
     * ```
     * Select *
     * FROM (resultsUnion) as RESULT_UNION
     * WHERE {product_id}-{kitting_id} IN (SELECT {product_id}-{kitting_id} FROM (filterUnion) as FILTER_UNION)
     * ```
     *
     * @return bool|\Illuminate\Database\Query\Builder will return false if it cant process
     * @throws CountryNotSetException if country is not set
     */
    public function unify ()
    {
        // cannot process this query without setting a country
        if (!isset($this->country)) {
            throw new CountryNotSetException(trans('message.country.not-set'));
        }
        if (!empty($this->productQuery) && !empty($this->kittingQuery)) {

            $resultsUnion = $this->getResultUnion();

            $filterUnion = $this->getFilterUnion()->limit($this->limit)->offset($this->offset);

            $productKittingConcat = "CONCAT(`product_id`, '-', `kitting_id`)";

            $resultsSQL = $resultsUnion->toSql();

            $resultsBindings = $resultsUnion->getBindings();

            $filterSQL = $filterUnion->toSql();

            $filterBindings = $filterUnion->getBindings();

            // Create the master union
            $unified = DB::table(DB::raw('(' . $resultsSQL . ') AS RESLUTS_UNION'));

            // Create the filtration union
            $unified->whereRaw(
                $productKittingConcat . " IN ( SELECT " . $productKittingConcat . " FROM (" .
                $filterSQL . ") AS FILTER_UNION )"
            );

            $unified->addBinding($resultsBindings, 'select');

            $unified->addBinding($filterBindings, 'where');

            return $unified;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get ($columns = ['*'])
    {
        if ($unionQuery = $this->unify()) {
            // TODO : Remove after testing finished
            $sql = $unionQuery->toSql();

            foreach ($unionQuery->getBindings() as $binding) {
                $value = is_numeric($binding) ? $binding : "'" . $binding . "'";

                $sql = preg_replace('/\?/', $value, $sql, 1);
            }

            return $this->collectProductsAndKitting($unionQuery->get());
        } else {
            return [];
        }
    }

    /**
     * @return int of total count
     */
    public function count ()
    {
        $filterUnion = $this->getFilterUnion();

        $filterSQL = $filterUnion->toSql();

        $filterBindings = $filterUnion->getBindings();

        $count = DB::table(DB::raw('(' . $filterSQL . ') AS RESULTS'));

        $count->addBinding($filterBindings, 'select');

        $count->select(DB::raw('count(*) as count'));

        return $count->first()->count;
    }

    /**
     * Processes the results collection for liner to a dimensional array with
     * prices, descriptions and images. It indexes the array returned in the sort order for API consumption
     *
     * @param $collection \Illuminate\Database\Eloquent\Collection
     * @return array
     */
    public function collectProductsAndKitting ($collection)
    {
        $nameGlobal = function ($item) {
            return $item->product_id . "-" . $item->kitting_id;
        };

        $reducer = [];

        // Reduce Products & Kittings
        $reducer = $collection->reduce(
            function ($carry, $item) use ($nameGlobal) {
                $itemFillables = [
                    "product_id",
                    "kitting_id",
                    "name",
                    "sku",
                    "is_dummy_code",
                    "created_at",
                    "favorites_id"
                ];

                $values = array_intersect_key(
                    get_object_vars($item),
                    array_map(
                        function () { return ""; }, array_flip($itemFillables)
                    ) // to ensure wrong values are not assigned
                );

                $values['id'] = intval(empty($values["kitting_id"]) ? $values["product_id"] : $values["kitting_id"]);

                $carry[$nameGlobal($item)] = $values;

                return $carry;
            }, $reducer
        );

        // reduce subs
        $names = [
            "images",
            "prices",
            "descriptions",
            "general_settings",
            "products_of_kitting"
        ];
        foreach ($names as $name) {
            $itemFillables = $this->namespacedFillableNames($name);

            $reducer = $collection->reduce(
                function ($carry, $item) use ($nameGlobal, $name, $itemFillables) {
                    $values = array_intersect_key(
                        get_object_vars($item), array_map(
                        function () {
                            return "";
                        }, array_flip($itemFillables)
                    ) // to ensure wrong values are not assigned
                    );

                    if (!isset($carry[$nameGlobal($item)][$name])) {
                        $carry[$nameGlobal($item)][$name] = [];
                    }

                    // Process Master data
                    if ($name == "general_settings" && isset($values[$name . "_master_id"])) {
                        $values[$name . '_id'] = $values[$name . "_master_id"] . $values[$name . "_master_data_id"];
                    }

                    // Process Price Data
                    if ($name == "prices" && isset($values[$name . "_gmp_price_gst"])) {
                        $values[$name . '_gmp_price_tax'] = $values[$name . "_gmp_price_gst"];

                        unset($values[$name . '_gmp_price_gst']);
                    }

                    // Process Price Data
                    if ($name == "prices" && isset($values[$name . "_rp_price_gst"])) {
                        $values[$name . '_rp_price_tax'] = $values[$name . "_rp_price_gst"];

                        unset($values[$name . '_rp_price_gst']);
                    }

                    if (!empty($values[$name . '_id'])) {
                        foreach ($values as $key => $value) {
                            $newKey = str_replace($name . "_", "", $key);

                            $values[$newKey] = $value;

                            unset($values[$key]);
                        }
                        $carry[$nameGlobal($item)][$name]["-" . $values['id']] = $values;
                        if ($name == "general_settings") {
                            unset($carry[$nameGlobal($item)][$name]["-" . $values['id']]['id']);
                        }
                    }

                    return $carry;
                }, $reducer
            );
        }

        // Re index array
        $index = 0;
        foreach ($reducer as $key => $reduced) {
            $reducer[$index] = $reduced;

            foreach ($names as $name) {
                $iIndex = 0;

                foreach ($reducer[$index][$name] as $iKey => $item) {
                    $reducer[$index][$name][$iIndex] = $item;

                    unset($reducer[$index][$name][$iKey]);

                    $iIndex++;
                }
            }

            $index++;

            unset($reducer[$key]);
        }

        return $reducer;
    }

    /**
     * Returns the set country Id
     *
     * @return int country id
     * @throws CountryNotSetException if country is not set
     */
    public function getCountryId ()
    {
        if ($this->country) {
            return $this->country;
        } else {
            throw new CountryNotSetException(trans("message.country.not-set"));
        }
    }

    /**
     * Returns the location that is set
     *
     * @return int location
     */
    public function getLocation ()
    {
        return $this->location;
    }

    /**
     * Dynamically build a join statement
     *
     * @param string|array $fromTable a first level key from $this->initializeTableNames() object build, to be used as table name. If an array is supplied, the first item in the array is considered as the table name to use and the second it is considered as the query to execute on
     * @param $toTable a second level key of $fromTable from $this->>initializeTableNames()
     * @param $toFieldId the filed name to be used in as JOIN {$fromTable} ON {$fromTable}.{$fromFieldId} = {$toTable}.{$toFieldId}
     * @param bool $callback a callback to be called within the join statement, the $join, fromTableName, toTableName, $addingToUnion (the union name its adding to) is passed as the first variable of the callback
     * @param string $activeFiled the name to be used for active field, if not specified it will use the name 'active'
     * @param string $fromFieldId from field id, to be used in the join statement for the from table field
     * @param string $joinType to make an inner join use "join" for left use "left"
     * @param string $addToUnions can be both | results | filters
     * @throws \Exception if $addToUnion is not valid
     */
    private function buildJoin (
        $fromTable, $toTable, $toFieldId, $callback = false, $activeFiled = "active", $fromFieldId = "id",
        $joinType = "leftJoin", $addToUnions = "both"
    )
    {
        if (!in_array($addToUnions, $this->addToUnionTypes)) {
            throw new \Exception($addToUnions . trans("message.query-building.unknown-union-type"));
        }
        // start the build process
        $onlyActive = $this->onlyActive;

        $addingToUnion = "results";
        // Dynamically select Query Builder

        // Select table and query
        if (is_array($fromTable)) {
            $quaryName = $fromTable[1];

            $fromTable = $fromTable[0];
        } else {
            $quaryName = $fromTable;
        }
        $as = property_exists(
            $this->tableNames->{$fromTable}->{$toTable}, 'as'
        ) ? $this->tableNames->{$fromTable}->{$toTable}->as : false;

        $joinFunction = function ($join) use (
            $onlyActive, $fromTable, $fromFieldId, $toFieldId, $toTable, $activeFiled, $callback, &$addingToUnion, $as
        ) {
            // dynamically create join on
            $join->on(
                $this->tableNames->{$fromTable}->name . '.' . $fromFieldId, '=',
                ($as ? $as : $this->tableNames->{$fromTable}->{$toTable}->name) . '.' . $toFieldId
            );
            // see if have to filter active records
            if ($onlyActive && !empty($activeFiled)) {
                $join->where(
                    ($as ? $as : $this->tableNames->{$fromTable}->{$toTable}->name) . '.' . $activeFiled, '=',
                    1
                );
            }
            // call back if required
            if ($callback) {
                call_user_func_array(
                    $callback, [
                                 $join,
                                 $this->tableNames->{$fromTable}->name,
                                 $this->tableNames->{$fromTable}->{$toTable}->name,
                                 $addingToUnion,
                                 $as
                             ]
                );
            }
        };

        if ($addToUnions === "both" || $addToUnions === "results") {
            $this->{$quaryName . "Query"}->{$joinType}(
                $this->tableNames->{$fromTable}->{$toTable}->name .
                ($as ? " as " . $as : ""), $joinFunction
            );
        }
        $addingToUnion = "filters";
        // Add to filters if build is started
        if ($this->buildStated && ($addToUnions === "both" || $addToUnions === "filters")) {
            $this->{$quaryName . "QueryFilter"}->{$joinType}(
                $this->tableNames->{$fromTable}->{$toTable}->name .
                ($as ? " as " . $as : ""), $joinFunction
            );
        }
    }

    /**
     * Builds the ORDER BY statement into the queries
     *
     * @param string $order ASC|DESC
     * @param $field the filed name to order
     * @param int|bool $position the position at with to put the join, higher position means the oder will be added later
     * @throws \Exception
     */
    private function buildOrder ($order, $field, $position = false)
    {
        $order = $this->orderType($order);
        if ($position) {
            $this->orders[$position] = [$order => $field];
        } else {
            $this->orders[] = [$order => $field];
        }
    }

    /**
     * Get fields for given sales types
     *
     * @param array $masterDataSalesTypeIds
     * @return array
     */
    private function getSaleTypeCvFields (array $masterDataSalesTypeIds)
    {
        $saleTypes = collect($masterDataSalesTypeIds)
            // filter the master data with the sales type ids used in the current query
            ->filter(function ($item, $masterDataId){
                return in_array($masterDataId, $this->filteredSalesTypesIds);
            })
            ->all();

        foreach ($saleTypes as $salesTypeId => $key) {
            if (!isset($this->saleTypeCvFields[$salesTypeId])) {
                $cvs = $this->getCvsForSaleType($key);

                if (count($cvs) > 0) {
                    $this->saleTypeCvFields[$salesTypeId] = $cvs;
                } else {
                    $this->saleTypeCvFields[$salesTypeId] = null;
                }
            }
        }

        return collect($this->saleTypeCvFields)
            ->filter(function ($item, $key) use ($saleTypes) {
                return in_array($key, array_flip($saleTypes)) && !is_null($item);
            })
            ->toArray();
    }

    /**
     * Creates the cv query based on conditions
     *
     * @param int $cv
     * @param array $masterDataSalesTypeIds
     * @param string $condition
     */
    private function cvQuery (int $cv, array $masterDataSalesTypeIds, string $condition)
    {
        foreach (['product', 'kitting'] as $table) {
            $whereQueries = [];

            $tableName = $this->tableNames->{$table}->prices->name;

            foreach ($this->getSaleTypeCvFields($masterDataSalesTypeIds) as $field => $cvs) {
                $cvsWithTable = array_map(function ($cv) use ($tableName) {
                    return "`" . $tableName . "`.`" . $cv . "`";
                }, $cvs);

                $whereQueries[] = DB::raw("(". join('+', $cvsWithTable) .") " . $condition . ' ' . $cv);
            }

            $this->{$table . "QueryFilter"}->where(function ($query) use ($whereQueries) {
                $type = 'whereRaw';

                foreach ($whereQueries as $whereQuery) {
                    $query->{$type}($whereQuery);

                    $type = 'orWhereRaw';
                }
            });

        }

        $this->cvFiltered = true;
    }

    /**
     * Get cv fields for given sales type
     *
     * @param string $saleTypeKey
     * @return mixed
     */
    private function getCvsForSaleType (string $saleTypeKey)
    {
        return collect($this->transactionTypeConfigCodes)
            ->filter(function ($title, $key) use ($saleTypeKey) {
                return strtolower($title) === strtolower($saleTypeKey);
            })
            ->pipe(function ($configSalesTypeKeys) {
                $keys = $configSalesTypeKeys->all();

                return collect($this->saleTypeCvSettings)
                    ->filter(function($item, $key) use ($keys) {
                        return isset($keys[$key]);
                    });
            })
            ->flatten()
            ->filter(function ($item, $key) {
                $keys = array_flip($this->cvAcronymCodes);

                $rnkCvs = array_flip($this->cvMechanism['upgrade']);

                return isset($keys[$item]) && !isset($rnkCvs[$item]);
            })
            ->map(function ($item, $key) {
                return array_flip($this->cvAcronymCodes)[$item];
            })
            ->values()
            ->all();
    }

    /**
     * Gets the cvs based on cv types
     *
     * @param array $masterDataSalesTypeIds
     * @return mixed
     */
    private function getCvsForCvTypes(array $masterDataSalesTypeIds)
    {
        return collect($masterDataSalesTypeIds)
            // filter the master data with the sales type ids used in the current query
            ->filter(function ($item, $masterDataId){
                return in_array($masterDataId, $this->filteredSalesTypesIds);
            })
            ->values()
            // map the master data values with the sales type mappings in config file
            ->map(function ($masterDataValue) {
                return array_search(strtolower($masterDataValue),  $this->transactionTypeConfigCodes);
            })
            // get all the cv acronyms used the the sales types
            ->pipe(function ($configSalesTypeKeys) {
                $keys = array_flip($configSalesTypeKeys->all());
                return collect($this->saleTypeCvSettings)
                    ->filter(function($item, $key) use ($keys) {
                        return isset($keys[$key]);
                    });
            })
            // filter and unique
            ->flatten()
            ->unique()
            // all the cvs if filtered sv is empty
            ->pipe(function ($salesTypeCvKeys) {
                $keysToUse = array_flip($salesTypeCvKeys->all());
                $acronyms = collect(array_flip($this->cvAcronymCodes));
                if (count($keysToUse) === 0){
                    return $acronyms;
                } else {
                    return $acronyms->filter(function($item, $key) use ($keysToUse) {
                        return isset($keysToUse[$key]);
                    });
                }
            })
            ->values()
            ->all();
    }

    /**
     * Validates and returns the order type
     *
     * @param string $type ASC|DESC
     * @return string ASC|DESC
     * @throws \Exception
     */
    private function orderType ($type)
    {
        if (in_array($type, $this->orderTypes)) {
            return $type;
        } else {
            throw new \Exception($type . trans("message.query-building.invalid-ordering-type"));
        }
    }

    /**
     * Builds the where statement into the queries
     *
     * @param $field the field name to use in the other statement
     * @param string $type the type of where to use, it can be where or orWhere
     * @param string|callable $condition the where condition to use or nested parameter groupings callable function($query, $value, $field, $tablename)
     * @param $value the value to use
     * @param bool $addToQuery add to the filter query or return the query
     * @param array ...$names the array of names to move in steps in the table names
     * @return array
     */
    private function buildCommonWhere ($field, $type = "where", $condition, $value, $addToQuery = true, ...$names)
    {
        $builds = [];

        foreach (
            [
                "product",
                "kitting"
            ] as $table
        ) {
            $tableName = $this->tableNames->{$table};

            foreach ($names as $name) {
                $tableName = $tableName->{$name};
            }

            foreach (
                [
                    "",
                    "Filter"
                ] as $union
            ) {
                if (is_callable($condition)) {
                    $this->{$table . "Query" . $union}->{$type}(
                        function ($query) use (
                            $condition, $value, $field, $tableName
                        ) {
                            $condition($query, $value, $field, $tableName->name);
                        }
                    );
                } else {
                    if (isset(array_flip($tableName->fillables)[$field])) {
                        if ($addToQuery) {
                            $this->{$table . "Query" . $union}->{$type}($tableName->name . "." . $field, $condition, $value);
                        } else {
                            $builds[] = [
                                'table' => $table,
                                'union' => $union,
                                'tableName' => $tableName,
                                'field' => $field,
                                'condition' => $condition,
                                'value' => $value
                            ];
                        }
                    }
                }
            }
        }

        return $builds;
    }

    /**
     * Joins kitting items to its relevant products
     */
    private function joinKittingToProducts ()
    {
        if (!$this->isKittingJoinedWithProducts) { // only process if the join is not created yet

            $this->buildJoin('kitting', "products", 'kitting_id', null, false, "id", "leftJoin", "both");

            // join dummies products to dummy
            $this->buildJoin(
                [
                    "kitting_products",
                    "kitting"
                ], "products", 'id', null, false, 'product_id', "leftJoin", "both"
            );

            // add select statements
            $this->buildSelectFromFillable('products_of_kitting');
        }
        $this->isKittingJoinedWithProducts = true;
    }

    /**
     * Builds a select statement to the queries
     *
     * @param $field field name
     * @param $as use field name ase
     * @param string $queryName
     * @param array ...$names names to walk in $this->tableNames to get to the selecting table
     */
    private function buildSelect ($field, $as, $queryName = "", ...$names)
    {
        // Initialize default selects
        $productNameRoot = $this->tableNames->product;

        $kittingNameRoot = $this->tableNames->kitting;

        // walk to name
        foreach ($names as $name) {
            if ($productNameRoot !== false && property_exists($productNameRoot, $name)) {
                $productNameRoot = $productNameRoot->{$name};
            } else {
                $productNameRoot = false;
            }
            if ($kittingNameRoot !== false && property_exists($kittingNameRoot, $name)) {
                $kittingNameRoot = $kittingNameRoot->{$name};
            } else {
                $kittingNameRoot = false;
            }
        }

        // add selects
        if ($productNameRoot) {
            if (is_callable($field)) {
                $select = $field($productNameRoot->name, $as);
            } else {
                $select = $productNameRoot->name . '.' . $field . ' as ' . $as;
            }

            $this->{'productQuery' . $queryName}->addSelect($select);
        } else {
            $this->{'productQuery' . $queryName}->addSelect(DB::raw("NULL as " . $as));
        }

        if ($kittingNameRoot) {
            if (is_callable($field)) {
                $select = $field($kittingNameRoot->name, $as);
            } else {
                $select = $kittingNameRoot->name . '.' . $field . ' as ' . $as;
            }

            $this->{'kittingQuery' . $queryName}->addSelect($select);
        } else {
            $this->{'kittingQuery' . $queryName}->addSelect(DB::raw("NULL as " . $as));
        }
    }

    /**
     * Build the select statement from fillables
     *
     * @param array ...$names
     */
    private function buildSelectFromFillable (...$names)
    {

        // Produce the fillable requirements
        list
            (
            $allFillables, $namespance, $productNameRoot, $kittingNameRoot
            ) = $this->namespacedFillableNamesProducer(
            ...$names
        );

        // process all the fillables to create selects
        foreach ($allFillables as $fillable) {

            if ($productNameRoot !== null) {
                // add to products field name
                $productsFieldName = (in_array($fillable, $productNameRoot->fillables)) ? sprintf(
                    '`%s`.`%s`',
                    $productNameRoot->name,
                    $fillable
                ) : 'NULL';
            } else {
                $productsFieldName = 'NULL';
            }

            if ($kittingNameRoot !== null) {
                // add to kitting field name
                $kittingsFieldName = (in_array($fillable, $kittingNameRoot->fillables)) ? sprintf(
                    '`%s`.`%s`',
                    $kittingNameRoot->name,
                    $fillable
                ) : 'NULL';

            } else {
                $kittingsFieldName = 'NULL';
            }

            $this->productQuery->addSelect(
                DB::raw(sprintf('%s as `%s%s`', $productsFieldName, $namespance, $fillable))

            );

            $this->kittingQuery->addSelect(
                DB::raw(sprintf('%s as `%s%s`', $kittingsFieldName, $namespance, $fillable))
            );
        }
    }

    /**
     * Builds the default queries
     */
    private function buildDefaults ()
    {
        $this->buildStated = true;

        // Initialize default selects
        $this->productQuery->select(
            $this->tableNames->product->name . '.id as product_id',
            DB::raw("'' as `kitting_id`"),
            $this->tableNames->product->name. '.name',
            $this->tableNames->product->name. '.sku',
            $this->tableNames->product->name. '.is_dummy_code',
            $this->tableNames->product->name . '.created_at as created_at'
        );
        $this->kittingQuery->select(
            DB::raw("'' as `product_id`"),
            $this->tableNames->kitting->name . '.id as kitting_id',
            $this->tableNames->kitting->name . '.name as name',
            $this->tableNames->kitting->name . '.code as sku',
            DB::raw("0 as `is_dummy_code`"),
            $this->tableNames->kitting->name . '.created_at as created_at'
        );

        $this->productQueryFilter = clone $this->productQuery;
        $this->kittingQueryFilter = clone $this->kittingQuery;

    }

    /**
     * Returns an array of combined fillables of products and kitting for a given table
     *
     * @param array ...$names names to work in the $this->tableName
     * @return array of names of fields namespaced
     */
    private function namespacedFillableNames (...$names)
    {
        list
            (
            $allFillables, $namespance
            ) = $this->namespacedFillableNamesProducer(...$names);

        array_walk(
            $allFillables, function (&$fillable) use ($namespance) {
            $fillable = $namespance . $fillable;
        }
        );

        return array_values($allFillables);
    }

    /**
     * The producer of namespaced fillabes
     *
     * The method combines fillabes from the corresponding table and and adds namespacing to it so that the
     * name fields wont conflict. the `stdclass` classe in the returns are $this->tableNames possitions to with
     * the method walked to
     *
     * @param array ...$names names to walk from in $this->tableNames
     * @return array of [$allFillables|array, $namespace|string, $productNameRoot|stdclass, $kittingNameRoot|stdclass]
     */
    private function namespacedFillableNamesProducer (...$names)
    {
        // Initialize default selects
        $productNameRoot = $this->tableNames->product;

        $kittingNameRoot = $this->tableNames->kitting;

        $namespance = "";

        // needle to name
        foreach ($names as $name) {
            if ($productNameRoot !== null) {
                if (property_exists($productNameRoot, $name)) {
                    $productNameRoot = $productNameRoot->{$name};
                } else {
                    $productNameRoot = null;
                }
            }
            if ($kittingNameRoot !== null) {
                if (property_exists($kittingNameRoot, $name)) {
                    $kittingNameRoot = $kittingNameRoot->{$name};
                } else {
                    $kittingNameRoot = null;
                }
            }

            $namespance .= $name . "_";
        }

        $allFillables = array_unique(
            array_merge(
                $productNameRoot !== null ? $productNameRoot->fillables : [],
                $kittingNameRoot !== null ? $kittingNameRoot->fillables : []
            )
        );

        return [
            $allFillables,
            $namespance,
            $productNameRoot,
            $kittingNameRoot
        ];
    }

    /**
     * Table name to use for this model
     *
     * The names and fillables are retrived from objects so that the query will work even though a name has changed.
     */
    private function initializeTableNames ()
    {
        $productPrice = new ProductPrice();

        $kittingPrice = new KittingPrice();

        $productImage = new ProductImage();

        $productDescription = new ProductDescription();

        $kittingImage = new KittingImage();

        $kittingDescription = new KittingDescription();

        $productCatagory = new ProductCategory();

        $kittingProducts = new KittingProduct();

        $productGeneralSetting = new ProductGeneralSetting();

        $kittingGeneralSetting = new KittingGeneralSetting();

        $dummy = new Dummy();

        $favorites = new Favorites();

        $productLocation = new ProductLocation();

        $location = new Location();

        $this->tableNames = (object)[
            "product" => (object)[
                "name" => $this->productsModel->getTable(),
                "fillables" => array_merge(
                    ['id'], $this->productsModel->getFillable()
                ),
                "prices" => (object)[
                    "name" => $productPrice->getTable(),
                    "fillables" => array_merge(
                        ['id'], $productPrice->getFillable()
                    )
                ],
                "promoLocation" => (object)["name" => (new ProductPromoLocation())->getTable()],
                "country" => (object)["name" => (new ProductActive())->getTable()],
                "images" => (object)[
                    "name" => $productImage->getTable(),
                    "fillables" => array_merge(
                        ['id'], $productImage->getFillable()
                    )
                ],
                "descriptions" => (object)[
                    "name" => $productDescription->getTable(),
                    "fillables" => array_merge(
                        ['id'], $productDescription->getFillable()
                    )
                ],
                "category" => (object)["name" => $productCatagory->getTable()],
                "general_settings" => (object)[
                    "name" => $productGeneralSetting->getTable(),
                    "fillables" => $productGeneralSetting->getFillable()
                ],
                "dummies_products" => (object)["name" => "dummies_products"],
                "dummy" => (object)[
                    "name" => $dummy->getTable(),
                    "fillables" => ['is_lingerie']
                ],
                "favorites" => (object)[
                    "name" => $favorites->getTable(),
                    "fillables" => ["id"]
                ],
                "product_locations" => (object)[
                    "name" => $productLocation->getTable(),
                    "as" => "main_product_locations"
                ]
            ],
            "kitting" => (object)[
                "name" => $this->kittingModel->getTable(),
                "fillables" => array_merge(
                    ['id'], $this->kittingModel->getFillable()
                ),
                "prices" => (object)[
                    "name" => $kittingPrice->getTable(),
                    "fillables" => array_merge(
                        ['id'], $kittingPrice->getFillable()
                    )
                ],
                "images" => (object)[
                    "name" => $kittingImage->getTable(),
                    "fillables" => array_merge(
                        ['id'], $kittingImage->getFillable()
                    )
                ],
                "descriptions" => (object)[
                    "name" => $kittingDescription->getTable(),
                    "fillables" => array_merge(
                        ['id'], $kittingDescription->getFillable()
                    )
                ],
                "products" => (object)["name" => $kittingProducts->getTable()],
                "products_of_kitting" => (object)[
                    "name" => "products_of_kitting",
                    "fillables" => array_merge(
                        ['id'], $this->productsModel->getFillable()
                    )
                ],
                "general_settings" => (object)[
                    "name" => $kittingGeneralSetting->getTable(),
                    "fillables" => $kittingGeneralSetting->getFillable()
                ],
                "dummies_products" => (object)[
                    "name" => "dummies_products",
                    "as" => "dummies_of_kitting_product"
                ],
                "favorites" => (object)[
                    "name" => $favorites->getTable(),
                    "fillables" => ["id"]
                ],
                "kitting_locations" => (object)[
                    "name" => "kitting_locations",
                    "as" => "main_kitting_locations"
                ]
            ],
            "kitting_products" => (object)[
                "name" => $kittingProducts->getTable(),
                "products" => (object)[
                    "name" => $this->productsModel->getTable(),
                    "as" => "products_of_kitting"
                ]
            ],
            "kitting_locations" => (object)[
                "name" => "main_kitting_locations",
                "locations" => (object)[
                    "name" => $location->getTable()
                ]
            ],
            "dummies_products" => (object)[
                "name" => "dummies_products",
                "dummy" => (object)["name" => $dummy->getTable()]
            ],
            "products_of_kitting" => (object)[
                "name" => "products_of_kitting",
                "dummies_products" => (object)[
                    "name" => "dummies_products",
                    "as" => "dummies_of_kitting_product"
                ]
            ],
            "product_locations" => (object)[
                "name" => "main_product_locations",
                "locations" => (object)[
                    "name" => $location->getTable()
                ]
            ],
            "dummies_of_kitting_product" => (object)[
                "name" => "dummies_of_kitting_product",
                "dummy" => (object)["name" => $dummy->getTable()]
            ],
        ];
    }
}
