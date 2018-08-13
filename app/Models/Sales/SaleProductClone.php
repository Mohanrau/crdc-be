<?php
namespace App\Models\Sales;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;

class SaleProductClone extends Model
{
    protected $table = 'sales_products_clone';

    protected $fillable = [
        'sale_id',
        'product_id', //master products
        'name',
        'sku',
        'uom'
    ];

    /**
     * get product details for a given saleProductCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

}
