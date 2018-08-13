<?php
namespace App\Models\Stockists;

use App\Models\Masters\MasterData;
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class ConsignmentOrderReturnProduct extends Model
{
    use HasAudit;

    protected $table = 'consignments_orders_returns_products';

    protected $fillable = [
        'consignment_order_return_id',
        'product_price_id',
        'available_quantity_snapshot',
        'quantity',
        'unit_gmp_price_gst',
        'unit_nmp_price',
        'gmp_price_gst',
        'nmp_price'
    ];

    /**
     * get consignment order return detail for a given consignmentOrderReturnProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function consignmentOrderReturn()
    {
        return $this->belongsTo(ConsignmentOrderReturn::class,'consignment_order_return_id');
    }

    /**
     * get consignment order return product clone for a given consignmentOrderReturnProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function consignmentOrderReturnProductClone()
    {
        return $this->hasOne(ConsignmentOrderReturnProductClone::class, 'consignment_order_return_product_id');
    }
}
