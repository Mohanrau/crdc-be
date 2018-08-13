<?php
namespace App\Interfaces\Shop;

use Illuminate\Contracts\Support\Arrayable;

interface ShopProductAndKittingFilteringInterface extends Arrayable
{
    /**
     * Filters Products together with kitting
     *
     * @param int $countryId country Id is required and used to filter the products by country
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
    );

    /**
     * Calculates values for the retrieved results
     *
     * @return $this
     */
    public function calculated();

    /**
     * Refines the retrieved fields
     *
     * @return $this
     */
    public function refine();

    /**
     * get products and kitting by ids
     *
     * @param int $countryId
     * @param int $locationId
     * @param array $productIds
     * @param array $kittingIds
     * @param int $active
     * @return $this
     */
    function getProductsAndKittingsByIds(
        int $countryId,
        int $locationId,
        array $productIds = [],
        array $kittingIds = [],
        int $active = 1
    );
}