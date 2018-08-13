<?php
namespace App\Models\Invoices;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Traits\HasAudit;
use App\Models\General\CWSchedule;
use App\Models\Masters\MasterData;
use App\Models\Sales\Sale;

class Invoice extends Model
{
    use HasAudit;

    protected $table = 'invoices';

    protected $fillable = [
        'sale_id',
        'cw_id',
        'aeon_payment_stock_release_status_id',
        'aeon_release_date',
        'stockist_daily_transaction_status_id',
        'released_date',
        'invoice_number',
        'document_number',
        'invoice_date',
        'reference_number',
        'self_collection_code'
    ];

    /**
     * get the sale detail for a given invoicesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class,'sale_id');
    }

    /**
     * get the cwSchedules for a given invoicesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }

    /**
     * get stockist daily transaction status Details for a given invoicesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockistDailyTransactionStatus()
    {
        return $this->belongsTo(MasterData::class,'stockist_daily_transaction_status_id');
    }

    /**
     * get aeon release status Details for a given invoicesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function aeonReleaseStatus()
    {
        return $this->belongsTo(MasterData::class,'aeon_payment_stock_release_status_id');
    }
}
