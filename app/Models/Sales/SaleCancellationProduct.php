<?php
namespace App\Models\Sales;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class SaleCancellationProduct extends Model
{
    use HasAudit;

    protected $table = 'sales_cancellations_products';

    protected $fillable = [
        'sale_cancellation_id',
        'sale_product_id',
        'product_id',
        'mapping_id',
        'mapping_model',
        'available_kitting_quantity_snapshot',
        'kitting_quantity',
        'available_quantity_snapshot',
        'quantity',
        'product_cv',
        'price',
        'buy_back_price'
    ];

    /**
     * get sale cancellation for a given saleCancellationProductsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleCancellation()
    {
        return $this->belongsTo(SaleCancellation::class);
    }

    /**
     * get sale product details for a given saleCancellationProductsObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleProduct()
    {
        return $this->belongsTo(SaleProduct::class);
    }
}
