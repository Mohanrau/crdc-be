<?php
namespace App\Models\Products;

use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Model;

class ProductName extends Model
{
    protected $table = 'product_names';

    protected $fillable = [
        'country_id',
        'entity_id',
        'product_id',
        'name'
    ];

    /**
     * get country details for a given productNameObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * get product details for the given productNameObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
