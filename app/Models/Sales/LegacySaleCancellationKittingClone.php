<?php
namespace App\Models\Sales;

use App\Models\Kitting\Kitting;
use Illuminate\Database\Eloquent\Model;

class LegacySaleCancellationKittingClone extends Model
{
    protected $table = 'legacies_sales_cancellations_kitting_clone';

    protected $fillable = [
        'sale_cancellation_id',
        'kitting_id',
        'code',
        'name',
        'available_quantity_snapshot',
        'quantity',
        'product_cv',
        'gmp_price_gst',
        'nmp_price',
        'total',
        'buy_back_price'
    ];

    /**
     * get legacy sale cancellation detail for a given legacySaleCancellationKittingCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleCancellation()
    {
        return $this->belongsTo(SaleCancellation::class,'sale_cancellation_id');
    }

    /**
     * get kitting details for a given legacySaleCancellationKittingCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitting()
    {
        return $this->belongsTo(Kitting::class, 'kitting_id');
    }

    /**
     * get legacy sale cancellation kitting products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(LegacySaleCancellationProduct::class, 'legacy_sales_cancellations_kitting_clone_id', 'id');
    }
}
