<?php
namespace App\Models\Stockists;

use App\Models\{
    Masters\MasterData,
    Users\User,
    Locations\StockLocation
};
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class ConsignmentOrderReturn extends Model
{
    use HasAudit;

    protected $table = 'consignments_orders_returns';

    protected $fillable = [
        'stockist_id',
        'stock_location_id',
        'workflow_tracking_id',
        'type_id',
        'transaction_date',
        'document_number',
        'total_gmp',
        'total_amount',
        'total_tax',
        'remark',
        'status_id',
        'action_by',
        'action_at',
        'confirmed_return_amount',
        'delivery_status_id',
        'warehouse_receiving_status_id',
        'warehouse_received_by',
        'warehouse_received_at',
        'updated_by'
    ];

    /**
     * get stockist detail for a given consignmentOrderReturnObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }

    /**
     * get location detail for a given consignmentOrderReturnObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockLocation()
    {
        return $this->belongsTo(StockLocation::class,'stock_location_id');
    }

    /**
     * get consignment order return type details for a given consignmentOrderReturnObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function consignmentOrderReturnType()
    {
        return $this->belongsTo(MasterData::class,'type_id');
    }

    /**
     * get consignment order return status details for a given consignmentOrderReturnObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MasterData::class,'status_id');
    }

    /**
     * get action by detail by given consignmentOrderReturnObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actionBy()
    {
        return $this->belongsTo(User::class,'action_by');
    }

    /**
     * get consignment order return warehouse receive status details for a given consignmentOrderReturnObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseReceiveStatus()
    {
        return $this->belongsTo(MasterData::class,'warehouse_receiving_status_id');
    }

    /**
     * get consignment order return product for a given consignmentOrderReturnObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function consignmentOrderReturnProduct()
    {
        return $this->hasMany(ConsignmentOrderReturnProduct::class, 'consignment_order_return_id');
    }

    /**
     * get consignment loose product item
     *
     * @return array
     */
    public function getConsignmentProducts()
    {
        return $this
            ->consignmentOrderReturnProduct()
            ->with('consignmentOrderReturnProductClone')
            ->get()
            ->map(function ($item){

                $item->product_id = $item['consignmentOrderReturnProductClone']['product_id'];

                $item->name = $item['consignmentOrderReturnProductClone']['name'];

                $item->sku = $item['consignmentOrderReturnProductClone']['sku'];

                $item->uom = $item['consignmentOrderReturnProductClone']['uom'];

                $item->available_quantity = ($item->available_quantity_snapshot == NULL) ? 0 :
                    $item->available_quantity_snapshot;

                $item->gmp_price_gst = $item->unit_gmp_price_gst;

                $item->nmp_price = $item->unit_nmp_price;

                $item->base_price = [
                    'gmp_price_tax' => $item->unit_gmp_price_gst,
                    'nmp_price' => $item->unit_nmp_price,
                    'base_cv' => 0,
                    'cv1' => 0,
                    'wp_cv' => 0
                ];

                return $item;
            })
            ->toArray();
    }
}
