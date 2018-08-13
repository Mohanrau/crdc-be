<?php
namespace App\Interfaces\Products;

use App\Interfaces\BaseInterface;
use Carbon\Carbon;

interface ProductInterface
{
    /**
     * import YY Products Api
     *
     * @param array $data
     * @return mixed
     */
    public function importYYProducts(array $data);

    /**
     *
     *
     * @param int $countryId
     * @param int $categoryId
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getProductsByFilters(
        int $countryId,
        int $categoryId = 0,
        int $active = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

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
     * @return mixed
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
    );

    /**
     * get product details for a given countryId and productId
     *
     * @param int $countryId
     * @param int $productId
     * @return mixed
     */
    public function productDetails(int $countryId, int $productId);

    /**
     * get the effective pricing for a product based on locations and startDate
     *
     * @param int $countryId
     * @param int $productId
     * @param array $locationsIds
     * @param null $startDate
     * @return mixed
     */
    public function productEffectivePricing(int $countryId, int $productId, array $locationsIds = [], $startDate = null);

    /**
     * update one record
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id);

    /**
     * delete product image for a given image id
     *
     * @param int $id
     * @return mixed
     */
    public function deleteProductImage(int $id);
}