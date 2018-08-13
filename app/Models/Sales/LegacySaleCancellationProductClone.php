<?php
namespace App\Models\Sales;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class LegacySaleCancellationProductClone extends Model
{
    use HasAudit;

    protected $table = 'legacies_sales_cancellations_products_clone';

    protected $fillable = [
        'legacy_sale_cancellation_product_id',
        'product_id',
        'name',
        'sku',
        'uom'
    ];

    /**
     * get legacy sale cancellation product clone product detail for a given legacySaleCancellationProductCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legacySaleCancellationProduct()
    {
        return $this->belongsTo(LegacySaleCancellationProduct::class,'legacy_sale_cancellation_product_id');
    }
}
