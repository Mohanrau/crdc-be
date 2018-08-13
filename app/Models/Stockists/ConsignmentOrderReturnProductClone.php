<?php
namespace App\Models\Stockists;

use App\Models\Masters\MasterData;
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class ConsignmentOrderReturnProductClone extends Model
{
    use HasAudit;

    protected $table = 'consignments_orders_returns_products_clone';

    protected $fillable = [
        'consignment_order_return_product_id',
        'product_id',
        'name',
        'sku',
        'uom'
    ];

    /**
     * get consignment order return product detail for a given consignmentOrderReturnProductCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function consignmentOrderReturnProduct()
    {
        return $this->belongsTo(ConsignmentOrderReturnProduct::class,'consignment_order_return_product_id');
    }
}
