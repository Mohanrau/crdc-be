<?php
namespace App\Repositories\Products;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Products\ProductCategoryInterface,
    Models\Products\ProductCategory,
    Models\Campaigns\EsacVoucher,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;

class ProductCategoryRepository extends BaseRepository implements ProductCategoryInterface
{
    use ResourceRepository;

    private $esacVoucherObj;

    /**
     * ProductCategoryRepository constructor.
     * @param ProductCategory $model
     * @param EsacVoucher $esacVoucher
     */
    public function __construct(ProductCategory $model, EsacVoucher $esacVoucher)
    {
        parent::__construct($model);

        $this->esacVoucherObj = $esacVoucher;

        $this->with = ['parent', 'childs'];
    }

    /**
     * get specified master with all masterData related
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id, $with = [])
    {
        $data =  $this->modelObj->with($with)->findOrFail($id);

        $data->parent = $data->parent()->get();

        $data->childs = $data->childs()->get();

        return $data;
    }

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
     * @return mixed|static
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
    )
    {
        $data = $this->modelObj
            ->with(['parent', 'childs']);

        if ($parentId !== null) {
            $data = $data->where('parent_id', $parentId); 
        }

        if ($forSales !== null) {
            $data = $data->where('for_sales', $forSales); 
        }

        if ($forMarketing !== null) {
            $data = $data->where('for_marketing', $forMarketing); 
        }

        if ($esacVoucherIds !== null) {
            $esacVouchers = $this->esacVoucherObj
                ->whereIn('id', $esacVoucherIds)
                ->get();

            $productCategoryIds = [];
            
            foreach($esacVouchers as $eacVoucher) {
                $esacPromotionProductCategories = $eacVoucher
                    ->esacPromotion
                    ->esacPromotionProductCategories;
                
                foreach($esacPromotionProductCategories as $esacPromotionProductCategory) {
                    array_push($productCategoryIds, $esacPromotionProductCategory['id']);
                }
            }

            $data = $data->whereIn('id', $productCategoryIds);
        }

        if ($active !== null) {
            $data = $data->where('active', $active); 
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data = $data
            ->orderBy($orderBy, $orderMethod);

        $data = ($paginate) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * List of categories that has products
     *
     * @return \App\Models\Products\ProductCategory[]
     */
    public function getShopCategories()
    {
        return $this->modelObj
            ->whereHas('products')
            ->orWhereHas('childs.products')
            ->orWhereHas('childs.childs.products')
            ->where('for_sales', true)
            ->active()
            ->get();
    }

}