<?php
namespace App\Models\Products;

use App\Helpers\Traits\HasAudit;
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasAudit;

    protected $table = 'product_categories';

    protected $appends = ['web_image_link', 'mobile_image_link', 'hierarchy'];

    protected $fillable = [
        'yy_category_id',
        'parent_id',
        'name',
        'code',
        'for_sales',
        'for_marketing',
        'web_image_path',
        'mobile_image_path',
        'active'
    ];

    /**
     * get parent id data for a given productCategoryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**get all the childs for a given productCategory
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childs()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    /**
     * Get the web image url.
     *
     * @return string
     */
    public function getWebImageLinkAttribute()
    {
        return Uploader::getFileLink('file', 'product_category_web_image', $this->web_image_path);
    }

    /**
     * Get the mobile image url.
     *
     * @return string
     */
    public function getMobileImageLinkAttribute()
    {
        return Uploader::getFileLink('file', 'product_category_mobile_image', $this->mobile_image_path);
    }

    /**
     * Relate products to product categories
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products() {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get the hierarchy of category in array.
     * [0] = board category
     * [1] = sub category
     * [2] = minor category
     *
     * @return mixed
     */
    public function getHierarchyAttribute()
    {
        $hierarchy = [];

        array_push($hierarchy, $this->name);

        $obj = $this->parent()->first();


        while(!empty($obj))
        {
            array_push($hierarchy, $obj->name);

            /** @var ProductCategory $obj */
            $obj = $obj->parent;
        }

        $hierarchy = array_reverse($hierarchy);

        return array_pad($hierarchy, 3, "");
    }
}
