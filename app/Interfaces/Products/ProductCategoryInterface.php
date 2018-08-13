<?php
namespace App\Interfaces\Products;

use App\Interfaces\BaseInterface;

interface ProductCategoryInterface extends BaseInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int|null $parentId
     * @param int|null $forSales
     * @param int|null $forMarketing
     * @param array $esacVoucherIds
     * @param int|null $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getProductCategoriesByFilters(
        int $parentId = null,
        int $forSales = null,
        int $forMarketing = null,
        array $esacVoucherIds = null,
        int $active = null,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * List of categories that has products
     *
     * @return \App\Models\Products\ProductCategory[]
     */
    public function getShopCategories();
}