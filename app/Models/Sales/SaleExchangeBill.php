<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SaleExchangeBill extends Model
{
    protected $table = 'sales_exchange_bills';

    protected $fillable = [
        'sale_exchange_id',
        'exchange_bill_number',
        'exchange_reference_number',
        'exchange_bill_date',
    ];

    /**
     * get saleExchange info for a given saleExchangeBillObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salesExchange()
    {
        return $this->belongsTo(SaleExchange::class, 'sale_exchange_id');
    }
}
