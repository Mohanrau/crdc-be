<?php
namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;

class ProductLocation extends Model
{
    protected $table = 'product_locations';

    protected $fillable = [
        'country_id',
        'entity_id',
        'product_id',
        'location_id'
    ];

    public $timestamps = false;
}
