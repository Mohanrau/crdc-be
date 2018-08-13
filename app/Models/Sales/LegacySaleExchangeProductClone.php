<?php
namespace App\Models\Sales;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class LegacySaleExchangeProductClone extends Model
{
    use HasAudit;

    protected $table = 'legacies_sales_exchanges_products_clone';

    protected $fillable = [
        'legacy_sale_exchange_product_id',
        'product_id',
        'name',
        'sku',
        'uom'
    ];

    /**
     * get legacy sale exchange product clone product detail for a given legacySaleExchangeProductCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legacySaleExchangeProduct()
    {
        return $this->belongsTo(LegacySaleExchangeProduct::class,'legacy_sale_exchange_product_id');
    }

    /**
     * get product details for a given salesProductsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(SaleProduct::class, 'product_id');
    }
}
