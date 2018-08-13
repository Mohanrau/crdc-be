<?php
namespace App\Models\Sales;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class LegacySaleCancellationProduct extends Model
{
    use HasAudit;

    protected $table = 'legacies_sales_cancellations_products';

    protected $fillable = [
        'sale_cancellation_id',
        'legacy_sales_cancellations_kitting_clone_id',
        'available_quantity_snapshot',
        'quantity',
        'product_cv',
        'gmp_price_gst',
        'nmp_price',
        'average_price_unit',
        'total',
        'buy_back_price'
    ];

    /**
     * get sale cancellation detail for a given legacySaleCancellationProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleCancellation()
    {
        return $this->belongsTo(SaleCancellation::class,'sale_cancellation_id');
    }

    /**
     * get legacy sale cancellation product clone for a given legacySaleCancellationProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function legacySaleCancellationProductClone()
    {
        return $this->hasOne(LegacySaleCancellationProductClone::class, 'legacy_sale_cancellation_product_id');
    }

    /**
     * get kitting clone detail for a given legacySaleCancellationProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legacySaleCancellationKittingClone()
    {
        return $this->belongsTo(LegacySaleCancellationKittingClone::class,'legacy_sales_cancellations_kitting_clone_id');
    }

}
