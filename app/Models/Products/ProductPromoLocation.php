<?php
namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;

class ProductPromoLocation extends Model
{
    protected $table = 'product_promo_locations';

    protected $fillable = [
        'promo_id',
        'location_id'
    ];
}
