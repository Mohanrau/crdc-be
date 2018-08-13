<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderService extends Model
{
    protected $table = 'delivery_order_services';

    protected $fillable = [
        'country_id',
        'name'
    ];
}
