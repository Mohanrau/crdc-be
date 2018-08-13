<?php
namespace App\Models\Kitting;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;

class KittingProduct extends Model
{
    protected $table = 'kitting_products';

    protected $fillable = [
        'kitting_id',
        'product_id',
        'quantity',
        'foc_qty'
    ];

    public $timestamps = false;

    /**
     * get product with price
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo

     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
