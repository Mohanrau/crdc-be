<?php
namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;

class ProductActive extends Model
{
    protected $table = 'product_active_countries';

    protected $fillable = [
        'country_id',
        'product_id',
        'ibs_active',
        'yy_active'
    ];

    /**
     * check if the product active is true
     *
     * @param $query
     * @return mixed
     */
    public function scopeIbsActive($query)
    {
        return $query->where('ibs_active', 1);
    }
}
