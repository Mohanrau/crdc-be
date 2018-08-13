<?php
namespace App\Models\Sales;

use App\Models\Masters\MasterData;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $table = 'delivery_orders';

    protected $fillable = [
        'sale_id',
        'sales_product_id',
        'service_id',
        'delivered_quantity',
        'delivery_order_number',
        'consignment_order_number',
        'status_code_id',
        'status_id'
    ];

    protected $with = [
        'service',
        'statusCode',
        'status'
    ];

    /**
     * get sale info for a given deliveryOrderObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * get product info for a given deliveryOrderObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(SaleProduct::class);
    }

    /**
     * get masterData info for a given deliveryOrderObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(MasterData::class, 'service_id');
    }

    /**
     * get masterData info for a given deliveryOrderObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statusCode()
    {
        return $this->belongsTo(MasterData::class, 'status_code_id');
    }

    /**
     * get masterData info for a given deliveryOrderObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MasterData::class, 'status_id');
    }
}
