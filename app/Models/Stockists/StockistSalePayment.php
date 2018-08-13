<?php
namespace App\Models\Stockists;

use App\Models\Masters\MasterData;
use App\Models\Payments\PaymentModeProvider;
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockistSalePayment extends Model
{
    use HasAudit;

    protected $table = 'stockists_sales_payments';

    protected $fillable = [
        'stockist_id',
        'payment_mode_provider_id',
        'transaction_date',
        'amount',
        'paid_amount',
        'adjustment_amount',
        'outstanding_amount',
        'updated_by'
    ];

    /**
     * get stockist detail for a given stockistSalePaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class,'stockist_id');
    }

    /**
     * get payment mode provider_id detail for a given stockistSalePaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentProvider()
    {
        return $this->belongsTo(PaymentModeProvider::class,'payment_mode_provider_id');
    }

    /**
     * get stockist sale payment transaction for a given stockistSalePaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stockistSalePaymentTransaction()
    {
        return $this->hasMany(StockistSalePaymentTransaction::class, 'stockist_sale_payment_id');
    }

}
