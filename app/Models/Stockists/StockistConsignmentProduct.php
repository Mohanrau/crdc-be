<?php
namespace App\Models\Stockists;

use App\Models\Products\Product;
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistConsignmentProduct extends Model
{
    use HasAudit;

    protected $table = 'stockists_consignments_products';

    protected $fillable = [
        'stockist_id',
        'product_id',
        'available_quantity'
    ];

    /**
     * get stockist detail for a given stockistConsignmentProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }

    /**
     * get stockist detail for a given stockistConsignmentProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}
