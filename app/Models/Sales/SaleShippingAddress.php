<?php
namespace App\Models\Sales;

use App\Models\Masters\MasterData;
use Illuminate\Database\Eloquent\Model;

class SaleShippingAddress extends Model
{
    protected $table = 'sales_shipping_addresses';

    protected $fillable = [
        'sale_id',
        'country_id',
        'delivery_method_id',
        'recipient_name',
        'mobile',
        'address',
        'shipping_index'
    ];

    /**
     * get the delivery method for a given saleShippingAddressObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deliveryMethod()
    {
        return $this->belongsTo(MasterData::class, 'delivery_method_id');
    }
}
