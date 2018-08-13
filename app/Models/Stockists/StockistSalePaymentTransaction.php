<?php
namespace App\Models\Stockists;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistSalePaymentTransaction extends Model
{
    use HasAudit;

    protected $table = 'stockists_sales_payments_transactions';

    protected $fillable = [
        'stockist_sale_payment_id',
        'paid_amount',
        'adjustment_amount',
        'remarks'
    ];

    /**
     * get stockist sale payment transaction for a given stockistSalePaymentTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockistSalePayment()
    {
        return $this->belongsTo(StockistSalePayment::class,'stockist_sale_payment_id');
    }
}
