<?php
namespace App\Models\Payments;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Sales\Sale,
    Currency\Currency
};

class Payment extends Model
{
    use HasAudit;

    protected $table = 'payments';

    protected $fillable = [
        'payment_mode_provider_id',
        'mapping_id',
        'mapping_model',
        'currency_id',
        'amount',
        'status',
        'is_external',
        'is_share',
        'payment_detail',
        'is_third_party_refund',
        'created_at',
        'updated_by'
    ];

    /**
     * get the sales details for a given salesPaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class,'mapping_id');
    }

    /**
     * get payment mode provider for a given SalePaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentModeProvider()
    {
        return $this->belongsTo(PaymentModeProvider::class);
    }
    
    /**
     * get currency data for a given SalePaymentObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
